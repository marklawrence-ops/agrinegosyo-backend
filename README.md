# AgriNegosyo: DTI-DA Price Compliance & Market Intelligence Bridge

* This system automatically parses the weekly DA Price PDF into JSON, compares it with MSME inventory prices, and triggers a Webhook alert if a retailer’s price exceeds the current SRP by >10%.
* It addresses SDG 2 (Zero Hunger) and SDG 9 (Industry, Innovation, and Infrastructure).

## Team Members
* **Mark Lawrence Lacdao** - Backend/API
* **Axyll Judd Picardal** - Data/Price Parsing
* **Aldrin Rey Taberara** - Frontend/MSME Dashboard 
* **Christian Catada** - Data Analysis/Database

## Technical Stack
* **Backend:** PHP (Laravel), MySQL 
* **Frontend:** React (Vite), Tailwind CSS
* **Security:** Hashed identifiers for data privacy

---

## 🚀 Getting Started: Local Setup Guide

### Prerequisites
Make sure you have the following installed on your machine:
* [XAMPP](https://www.apachefriends.org/) or Laragon (for PHP and MySQL)
* [Composer](https://getcomposer.org/) (for Laravel dependencies)
* [Node.js](https://nodejs.org/) (for Frontend dependencies)
* Git

### Part 1: Backend (Laravel API) Setup
1. **Clone the repository:**
   ```bash
   git clone <your-backend-repo-url>
   cd agrinegosyo-backend
   ```

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Environment Setup:**
   
   Copy the example env file: cp .env.example .env

   Generate the app key: php artisan key:generate

   Open .env and configure your database:

      Code snippet
      
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=agrinegosyo
        DB_USERNAME=root
        DB_PASSWORD=

   Add the Discord Webhook for alerts:

      Code snippet

        ALERT_WEBHOOK_URL="your_discord_webhook_url_here"

4. **Run Database Migrations:**
    *(Ensure MySQL is running via XAMPP before doing this)*

    ```bash
    php artisan migrate:fresh --seed
    ```
    
   Start the Backend Server:
    ```bash
    php artisan serve
    ```

    The API will now be running at http://127.0.0.1:8000

### Part 2: Frontend (React/Vite) Setup

1. **Clone the repository:**
    *(Open a new terminal window)*
   
    ```bash
    git clone <your-frontend-repo-url>
    cd agrinegosyo-frontend
    ```

3. **Install dependencies:**

    ```bash
    npm install
    ```

5. **Start the Frontend Server:**
   *(Note: The legacy-peer-deps flag is required to bypass a known strict version conflict between Vite 8 and Tailwind CSS)*
   
    ```bash
   npm install --legacy-peer-deps
    ```

The dashboard will now be visible at http://localhost:5173

### 🛠️ Commands for Testing

Trigger the DA Price Parser:

To manually test the CSV parsing and trigger Discord alerts for overpricing violations, run:

  ```bash
  php artisan prices:fetch-da
  ```
