#!/bin/bash
# S.I.M.S Database Setup Script
# This script imports the database schema into MySQL

echo "===================================="
echo "S.I.M.S - Database Setup"
echo "===================================="
echo ""

# Check if schema.sql exists
if [ ! -f "database/schema.sql" ]; then
    echo "Error: database/schema.sql not found!"
    exit 1
fi

# Prompt for MySQL credentials
read -p "Enter MySQL username (default: root): " db_user
db_user=${db_user:-root}

read -sp "Enter MySQL password: " db_pass
echo ""

# Import the schema
echo "Importing database schema..."
mysql -u "$db_user" -p"$db_pass" < database/schema.sql

if [ $? -eq 0 ]; then
    echo "✓ Database setup completed successfully!"
    echo "✓ Database: internship_db"
    echo "✓ Tables created"
else
    echo "✗ Error importing database schema"
    exit 1
fi

echo ""
echo "===================================="
echo "Setup Complete!"
echo "===================================="
echo ""
echo "Access the system at:"
echo "http://localhost/S-I-M-S/frontend/index.php"
echo ""
