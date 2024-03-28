# AutoDB
## An automatic model generator for database

The AutoDB is a tool that generates a database model from a given connection in .env file. The tool is designed to be used in the context of huges amount of data when migrating for Laravel or automatize a repetitive task. The tool takes the dataset from the database and generates a model with the attributes and cast for each column.

### Installation
```composer require leivingson/autodb```

### Usage
```php artisan autodb:generate```


