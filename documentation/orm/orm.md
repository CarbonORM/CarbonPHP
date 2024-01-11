# C6 is shipped with a custom ORM which generates PHP code and Typescript MYSQL bindings.

Writing sql in code is a long process which is hard to maintain over time. C6 automates that. When a reference no longer exists in MYSQL it will not be generated. Your editor will highlight it as undefined, giving you the opportunity to fix it. With code references generated for you writing your sql is easier than ever. Statement and columns will autocomplete giving you ease of mind every time. Queries generated will be validated automatically using PDO based off table data from the mysql dump. The REST ORM C6 ships with allows gives you a full api with customizable endpoints and validation functions.



The command line interface is used to generate and regenerate bindings.

```bash
php index.php rest
```

You may append the "-help" flag to see a full list or options.


Overview

0) Examples
1) Requirements
2) Internal API
3) External API
4) Validation Functions
    - Restful
      1) Column Regular Expressions
      2) Filter Every Request
      3) Filter Specific Request Methods
      4) Column Specific Filters
    - Internal
      1) Access Control with Delegated Administration
5) Data Retention
6) Session Management
7) Entity 
