# Esprit-PIDEV-2A15-2027-VitaCalc
Developed at Esprit School of Engineering – Tunisia. Academic Year 2026–2027. PHP, MVC, MySQL, HTML, CSS, JavaScript.
# VitaCalc – Wellness and Sports Management Website

## Overview

VitaCalc is a web application dedicated to wellness, health, nutrition and sports management.

This project was developed as part of the PIDEV – 2nd Year Engineering Program at **Esprit School of Engineering** – Tunisia (Academic Year 2026–2027).

The platform provides users with several tools and services to improve their daily lifestyle, monitor their sports activities and manage their nutrition.

## Features

The platform is divided into seven main modules:

1. **Advice**: provides useful wellness and lifestyle recommendations, organized by category.
2. **Mini Shop**: allows users to browse and manage healthy products.
3. **Sports Tracking**: helps users track their sports programs and exercises.
4. **Nutrition**: provides nutrition-related information (diets and nutritional goals).
5. **Meal Prep**: helps users organize their meals.
6. **Events**: provides information about sports and wellness events and registrations.
7. **User Authentication**: allows users to create an account, sign in, log out and manage their personal account.

## Tech Stack

### Frontend

- HTML
- CSS
- JavaScript
- Bootstrap (Front Office and Back Office templates)

### Backend

- PHP (MVC architecture)
- MySQL (relational database with foreign key relationships)

## Architecture

VitaCalc follows the **MVC (Model – View – Controller)** architecture and is split into two interfaces:

- **Front Office**: the public interface used by visitors.
- **Back Office**: the administration interface used to manage the content.

The application is organized into seven functional modules:

- Advice Management
- Mini Shop Management
- Sports Tracking Management
- Nutrition Management
- Meal Prep Management
- Events Management
- User Authentication

The database uses relational tables and foreign keys to connect related data. Each module was designed to be developed independently before the final integration.

## Contributors

This project is developed by a team of six students:

- Noureddine – [@nourekin88-lgtm](https://github.com/nourekin88-lgtm)
- Tasnim– [@yahmaditasnim3-web](https://github.com/yahmaditasnim3-web)
- Maicem – [@maicem](https://github.com/maicem)
- Yassmine – [@jaaferyasmine6-cmyk](https://github.com/jaaferyasmine6-cmyk)
- Anas – [@anash115](https://github.com/anash115)
- Karim – [@alouikarim817-collab](https://github.com/alouikarim817-collab)

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**.

- Academic Year: 2026–2027
- Program: PIDEV
- Class: 2A15

## Getting Started

1. Clone this repository:
   ```bash
   git clone https://github.com/<your-username>/<repository-name>.git
   cd <repository-name>
   ```
2. Install a local PHP environment (XAMPP or WAMP) and place the project folder in `htdocs` (XAMPP) or `www` (WAMP).
3. Start Apache and MySQL from the XAMPP/WAMP interface.
4. Configure the database connection in the configuration file (`config.php`).
5. Import the database structure (the `.sql` file) with phpMyAdmin.
6. Start your local PHP server (or use `php -S localhost:8000`).
7. Open the website in your browser: `http://localhost/<project-folder>/index.php`.

## Acknowledgments

We would like to thank our supervisors and **Esprit School of Engineering** for their academic guidance and support.
