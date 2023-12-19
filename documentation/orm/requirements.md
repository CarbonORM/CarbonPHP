# Requirements

By default the rest program uses mysqldump which should be in your environments $PATH. This will be the case should 
mysql be installed on your system. You may use the -mysqldump flag to specify the executable location. For some systems 
this is not possible, so the flag -dump exists to specify the location of the dump generated. This dump should be 
created using the --no-data flag for the mysqldump program. Not doing such may cause unexpected results. Should a dump 
file be provided, no database access or credentials are required. The following code example is our minimal rest example, 
which would give full rest access to any generated tables.

