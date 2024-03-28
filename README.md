<p align="center">
  <img src="https://github.com/Kaue-Romero/auto-db/assets/69368947/e33cfa5c-5457-471d-a98d-abaf72eea6dc" alt="example" />
</p>
<p align="center">
  <img src="https://img.shields.io/badge/Packagist-F28D1A?style=for-the-badge&logo=Packagist&logoColor=white" alt="version"/>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="laravel"/>
</p>

# AutoDB
## An automatic model generator for database

The AutoDB is a tool that generates a database model from a given connection in .env file. The tool is designed to be used in the context of huges amount of data when migrating for Laravel or automatize a repetitive task. The tool takes the dataset from the database and generates a model with the attributes and cast for each column.

### Installation
```composer require leivingson/autodb```

### Usage
```php artisan autodb:generate```

### Database Supports

![mysql](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

