#!/bin/bash

echo "Installing development requirements..."

# Install Homebrew if not installed
if ! command -v brew &> /dev/null; then
    echo "Installing Homebrew..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    
    # Add Homebrew to PATH
    echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
    eval "$(/opt/homebrew/bin/brew shellenv)"
else
    echo "Homebrew already installed"
fi

# Install PHP
echo "Installing PHP 8.1..."
brew install php@8.1

# Install Composer
echo "Installing Composer..."
brew install composer

# Install Node.js and npm
echo "Installing Node.js and npm..."
brew install node

# Install MySQL
echo "Installing MySQL..."
brew install mysql

# Start MySQL service
echo "Starting MySQL service..."
brew services start mysql

echo "Installation complete!"
echo ""
echo "Next steps:"
echo "1. Set up MySQL root password:"
echo "   mysql_secure_installation"
echo ""
echo "2. Create the database:"
echo "   mysql -u root -p -e 'CREATE DATABASE allowance_tracker;'"
echo ""
echo "3. Import the schema:"
echo "   mysql -u root -p allowance_tracker < database/schema.sql"
echo ""
echo "4. Copy the config file:"
echo "   cp config.example.php config.php"
echo ""
echo "5. Edit config.php with your database credentials and API keys"
echo ""
echo "6. Install project dependencies:"
echo "   composer install"
echo "   npm install"
