# File Structure & System Architecture

Historically, CarbonPHP specifically started as a simple Controller -> Model -> View framework. With my first solo 
application attempt I found the model layer increasingly verbose with repetitions difficult to abstract in a way which 
stayed readable. I analysed what it would take to simplify the logic for PDO operations in a semantically pleasing and 
programmatically safe way. As this dream evolved into a full REST MySQL ORM it was necessary to include functional hooks
that could modify or validate queries further than what an automated system could possibly provide. The art of generating
code to be tracked in a public repository, especially one with multiple team members, is a subtle beauty. Through many 
generations the models added to your code are generally considered final with no need for code changes or feature 
degradation with iterations of CarbonPHP and its inner workings.

1) Create/Edit your database schema and tables with all columns and relations you would like
2) Add CarbonPHP to a project file
   
2) Generate you PHP bindings
   



After 'generally' completing this ORM and deciding react is awesome, I found it a logical step to run these queries 
directly from the frontend. A few challenges quickly arose again:



1) Typing out SQL sucks
2) Rewiting 