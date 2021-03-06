InterLoS website with online scoring system
-------------------------------------------

Authors: Jan Papoušek and Jan Drábek

Maintenance: Jan Drábek <me@jandrabek.cz>

Requirements
------------

- PHP >= 5.5
- PHP in congfiguration to run Nette framework, see http://doc.nette.org/cs/requirements
- MySQL (MariaDB) database

Hardware
--------

With 100 teams and about 400 players, there is always an initial peak in load, however then it drops and starts raising in the second half of the
competition (probably due to database cron job.)

Tested configuration: 4 CPU, 4 GB RAM virtual server.

Installation
------------

1. Checkout this repository
2. Set WWW dir to /public/
3. Run `composer update` from root. When installing production server use `--no-dev` to use minified nette.
4. Copy /app/config/config.local.neon.example to /app/config/config.local.neon and overwrite parameters of config.neon (DB credentials...)
5. Make directories /temp and /log writable
6. Create database schema executing /resources/db/tables.sql and /resources/db/views.sql
7. Create temporary tables


Config
------
- Set mails which are used for sending mail
- Set cron and admin keys (randomly generated long password)
- Set database credentials


Creating new contest
--------------------

1. Create contest and set dates in table `year`:
   Name - year, like 2013, used for sorting to get latest year
   Registration start
   Registration end - beware of crossed dates
   Game start
   Game end - beware of crossed dates
2. Test registration by registering first team
3. To run

Cron example
------------

```
# Každou minutu
* * * * * wget -O /dev/null http://interlos-devel.fi.muni.cz/cron/database?cron-key=xyh88QhcVIZNuOJFTtlX > /dev/null 2>&1
# Každých pět minut
*/5 * * * * wget -O /dev/null http://interlos-devel.fi.muni.cz/cron/database?cron-key=xyh88QhcVIZNuOJFTtlX > /dev/null 2>&1
```

Running the contest
-------------------

0. Notify infrastructure operators about future usage (describe event, estimate number of people, describe amount of resources, request contact to technican which will be available)
1. Create series of task (ciphers, logical, programming) in table `serie` (fill `to_show` properly, to prevent answering before release whole series)
2. Populate table `task` with correct answers. Provide codes in upper case (just for case)
3. Update config - set page with in game information which will be shown below the header after the game beginning.
4. Udpate page with proper URLs where the files are hosted.
5. Run CRON job (generating statistics)
   /cron/database?admin-key=hesloproadmin OR /cron/database?cron-key=hesloprocron NEVER BOTH OF THEM
6. Run it! Test answering with testing team and then remove the answer.
7. Monitor server performance.
8. Disable CRON jobs

Performance
-----------

Typical configuration before 2014 was 4 GB RAM, 4 CPUs which made the game totally ok for 150 teams.
However there was a higher peak at game start due high IO.

In 2016 we used configuration with 6 GB RAM and 6 CPUs (just beacuse our limit have allowed that) and it was totally ok for 200 teams, not even high peak was visible at game start. 

File hosting
------------

Prepare at least two servers with Apache and PHP (.htaccess needed). Prepare and test them in advance, so links to all series will be available at least 
30 minutes before (due to high load when game starts).

Revealing script
----------------

Prepare .htaccess file:
```
Deny from all
<FilesMatch "^$">
	Allow from all
</FilesMatch>
<FilesMatch "index.php">
	Allow from all
</FilesMatch>
```

Prepare index.php for each serie:
```
<?php
date_default_timezone_set('Europe/Prague');
if(new DateTime() >= new DateTime("2013-11-30 15:00:00")) {
	@rename(".htaccess",'.htaccess-old');
} else {
    die('Prvni sada bude zpristupnena az v 15:00.');
}
?>
Page content
```

Test:
1. that you can access index.php with index.php and / path
2. that PHP is running
3. that you cannot access other files in directory by direct path
4. that scripts works - renamess .htaccess in proper time (permission to the parent dir must be set correctly).


Running the contest
-------------------

1. Disable CRON job.
2. Manually trigger (last) update of statistics.
3. Update homepage with results.
4. Commit whole year directory (archive also in-game information to the root of this folder; static html files recommended)
5. Save table with full statistics into static HTML (see previous yearXXXX).
6. Save list of teams into static HTML (see previous teamsYearXXXX).
7. Add entry to archive page about the year.