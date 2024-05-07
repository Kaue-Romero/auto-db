<p align="center">
  <img src="https://github.com/Kaue-Romero/auto-db/assets/69368947/358081a3-8e9e-4b2b-bd8b-ed004ea2af4c" alt="example" />
</p>
<p align="center">
  <img src="https://img.shields.io/badge/Packagist-F28D1A?style=for-the-badge&logo=Packagist&logoColor=white" alt="version"/>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="laravel"/>
</p>

# MySQL AutoDB
## An automatic migrations and models generator for MySQL database

The AutoDB is a tool that generates a database model from a given connection in .env file. 
The tool is designed to be used in the context of huges amount of data when migrating for Laravel or automatize a repetitive task. 
The project takes the dataset from the database and generates the migrations and models with the attributes and cast the fields in models for each column.
The code uses Levenshtein Algorithm to translate MySQL types to Eloquent types.

### Installation
```composer require leivingson/autodb```

### Usage
```php artisan autodb:generate```

### Database Supports

![mysql](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

