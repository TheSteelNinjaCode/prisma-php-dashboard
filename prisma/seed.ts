import { PrismaClient } from "@prisma/client";
import { PrismaBetterSqlite3 } from "@prisma/adapter-better-sqlite3";

const adapter = new PrismaBetterSqlite3({
  url: process.env.DATABASE_URL!,
});
const prisma = new PrismaClient({ adapter });

// ============================================================
// 2. DATA DEFINITION
// ============================================================

const userRoleData = [
  { id: 1, name: "Admin" },
  { id: 2, name: "User" },
];

const userData = [
  {
    name: "Juan",
    email: "j@gmail.com",
    password: "$2b$10$mgjotYzIXwrK1MCWmu4tgeUVnLcb.qzvqwxOq4FXEL8k2obwXivDi", // bcrypt: 1234
    roleId: 1,
  },
];

// ============================================================
// 3. EXECUTION LOGIC
// ============================================================

async function main() {
  console.log(`Start seeding ...`);

  await prisma.user.deleteMany();
  await prisma.userRole.deleteMany();

  await prisma.userRole.createMany({ data: userRoleData });
  await prisma.user.createMany({ data: userData });

  console.log(`Seeding finished.`);
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
