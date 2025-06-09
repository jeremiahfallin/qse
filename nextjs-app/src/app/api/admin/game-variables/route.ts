import { NextResponse } from 'next/server';
import { PrismaClient, DbVar } from '@prisma/client';
import { ensureAdmin } from '@/lib/auth/adminUtils'; // Adjust path as needed

const prisma = new PrismaClient();

export async function GET(request: Request) {
  const adminAuthResponse = await ensureAdmin();
  if (adminAuthResponse) {
    return adminAuthResponse;
  }

  try {
    const { searchParams } = new URL(request.url);
    const typeFilter = searchParams.get('type');

    const whereClause: any = {
      NOT: {
        name: 'rejoin_delay', // Exclude rejoin_delay as per PHP
      }
    };

    if (typeFilter !== null && typeFilter !== undefined && typeFilter !== '') {
      const typeNum = parseInt(typeFilter, 10);
      if (!isNaN(typeNum)) {
        whereClause.type = typeNum;
      } else {
        return NextResponse.json({ message: 'Invalid type filter provided.' }, { status: 400 });
      }
    }

    const gameVariables = await prisma.dbVar.findMany({
      where: whereClause,
      orderBy: {
        name: 'asc',
      },
    });

    return NextResponse.json(gameVariables, { status: 200 });

  } catch (error) {
    console.error('Admin Get Game Variables error:', error);
    return NextResponse.json({ message: 'An error occurred while fetching game variables.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}

export async function PUT(request: Request) {
  const adminAuthResponse = await ensureAdmin();
  if (adminAuthResponse) {
    return adminAuthResponse;
  }

  try {
    const body = await request.json();
    // Expected body: { variables: Array<{ name: string, value: string }> }
    const updates = body.variables;

    if (!Array.isArray(updates)) {
      return NextResponse.json({ message: 'Request body must be an object with a "variables" array.' }, { status: 400 });
    }

    const validationErrors: string[] = [];
    const validUpdates: Array<{ name: string, value: string, dbVarInfo: DbVar }> = [];

    for (const update of updates) {
      if (typeof update.name !== 'string' || typeof update.value !== 'string') {
        validationErrors.push(`Invalid format for variable: ${JSON.stringify(update)}.`);
        continue;
      }

      const dbVarInfo = await prisma.dbVar.findUnique({ where: { name: update.name } });
      if (!dbVarInfo) {
        validationErrors.push(`Variable "${update.name}" not found.`);
        continue;
      }

      // Perform validation based on DbVar.type, min, max
      // Assuming DbVar.type: 1=integer, 2=boolean (0/1), 0=string (or other types as defined in game)
      let processedValue: string | number | boolean = update.value;
      let isValid = true;

      // Type field in DbVar is TINYINT(3) - values (0-8) found in new_game.sql for QUANTUM_db_vars
      // 0: string, 1: boolean/flag (0/1), 2: integer, 3: float/decimal,
      // (other values like 4,5,6,7,8,9 exist for specific game logic categories but value itself is often numeric/flag)
      // For simplicity, we'll primarily validate numeric types against min/max if they are numbers.
      // The PHP code directly compared strings in SQL: `'$value' >= min && '$value' <= max`
      // This can lead to issues e.g., "10" < "9". Correct validation needs type conversion.

      const varType = dbVarInfo.type; // This is the category type, not necessarily data type of 'value'
      const minValue = parseFloat(dbVarInfo.min as string); // min/max are stored as int/unsigned int, but value is string
      const maxValue = parseFloat(dbVarInfo.max as string); // value is string, min/max are int in DB
      const numValue = parseFloat(update.value);

      // General numeric validation if min/max look like numbers
      if (!isNaN(minValue) && !isNaN(maxValue)) {
        if (isNaN(numValue)) {
          isValid = false;
          validationErrors.push(`Variable "${update.name}" received non-numeric value "${update.value}" but numeric range [${minValue}-${maxValue}] expected.`);
        } else if (numValue < minValue || numValue > maxValue) {
          isValid = false;
          validationErrors.push(`Value for "${update.name}" (${numValue}) is out of range [${minValue}-${maxValue}].`);
        }
        if (isValid) processedValue = String(numValue); // Store as string, but validated as number
      }
      // Add more type-specific validation here if DbVar.type was more granular for data types
      // e.g. for boolean (type 1 in some contexts)
      if (varType === 1 && !(update.value === "0" || update.value === "1")) {
         // If it's meant to be a boolean flag based on its category type
         // validationErrors.push(`Value for "${update.name}" must be 0 or 1.`);
         // isValid = false;
         // For now, allow string "0" or "1" if it passes general numeric check if min/max are 0/1
      }


      if (isValid) {
        validUpdates.push({ name: update.name, value: String(processedValue), dbVarInfo });
      }
    }

    if (validationErrors.length > 0) {
      return NextResponse.json({ message: 'Validation failed.', errors: validationErrors }, { status: 400 });
    }

    // Perform updates in a transaction
    await prisma.$transaction(
      validUpdates.map(upd =>
        prisma.dbVar.update({
          where: { name: upd.name },
          data: { value: upd.value },
        })
      )
    );

    // TODO: Implement logic equivalent to require_once('includes/build_vars.php');
    // This would involve fetching all DbVars and writing them to a TypeScript or JSON config file.
    // console.log("TODO: Regenerate static config file if var_source == 0");


    // Log this admin action
    // await prisma.userHistory.create({ data: { login_id: adminUserId, action: 'Updated Game Vars', ... } });

    return NextResponse.json({ message: 'Game variables updated successfully.' }, { status: 200 });

  } catch (error) {
    console.error('Admin Update Game Variables error:', error);
    return NextResponse.json({ message: 'An error occurred while updating game variables.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
