# Entity System

Popular in game development the entity system is C6's bread and butter. In short: it allows us to relate any table to 
any other table in a meaningful way where cascade delete will still work. To clarify, if you have a locations table, you 
might want to use that for user images uploaded, and shipping address for your customer. When that photo, or user, gets 
deleted you would want wall relations to that entity (the user or picture) to be delete. Another example would be a 
'like button'. This could be stuck to any entity. I like the person, location, organization, photo, ect...

The way C6 achieves this system in mysql is simple. We have a master table called 'carbons' which contains every primary 
key in the whole schema. Actually every primary key will be generated with this table and then only referenced through 
foreign key relations. Tables will still have primary keys, and indexes will not change, but every relation will stem 
from a singular table. The 'carbons' table contains three columns: entity_pk, entity_fk, entity_tag. The entries to this 
table are entirely managed by the ORM generated code. The keys are binary(16) fields for maximum speed in searching. All 
tables with primary keys Must have cascade delete enabled for those relations. Keys are generated in mysql using the uuid() 
function then automatically hexed and unhexed for you through the api. To clarify there is no need to use the hex and unhex 
aggregate function on binary content as it is done for you in the API.

In our written example above we discussed the idea of user, photos, and likes. Let's look at what those would look like 
in the database. Users are almost always the top level entity in our system. I would argue that while many companies hold 
a reasonable technical flow that users belonging to an organization, there is always at least one user who should manage 
it. For this reason when the user gets created the reference in the 'carbons' table has entity_pk filled and entity_fk set 
to null. The entity_tag will always be the table's name that created the reference. From here out our user who creates 
the entity would have their own users entity_pk equal the entity_fk of entities they created. Exceptions to this rule 
exist such as when a users content they posted would not there after belong to them.

It is a good idea to create reference tables. When two tables need to be related together and because of the entity 
system are referenced, it is a good idea to export this. By this I mean have a table contain two columns, both of which 
point to 'carbons.entity_pk' with the cascade delete foreign keys. This helps reduce the searches in carbons, and 
shrinks the volume of your searches. When a reference be made, say for example: a known popular location is tagged to a
photo, this type of relation could be used. It would be fair to assume in some systems that photo's become open source 
and locations are other entities which do not belong to users. These could, in theory, be related together in the entity 
system. I Typically would recommend giving each table a primary key, with it pointing to carbons.entity_pk. Over time, 
you will notice when pk will not be use due to your own systems needs and relations. It doesn't hurt to have it in 
development, however overtime it's best to optimise where possible.




# References
https://stackoverflow.com/questions/8112831/implementing-comments-and-likes-in-database/8113064#8113064
