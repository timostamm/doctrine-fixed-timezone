Fixed timezone types for Doctrine 
=================================


### The problem

If your application handles dates with different timezones, you 
have two options:


### 1) save the time zone of each date to the database

If you need to know the timezone later on, this is the only option. 

Unfortunately, this makes queries on dates practically impossible:
`SELECT * FROM x WHERE x.date > CURRENT_TIMESTAMP()` will lead to 
unexpected results. CURRENT_TIMESTAMP will use the current time of
the database server, and your dates are interpreted in the wrong 
timezone.


### 2) Convert all dates saved in the database to one timezone

If you convert all dates to the timezone of the database server, 
your queries will work as expected. 

Unfortunately, Doctrine does not come with an option to do this 
conversion. 


### Solution provided by this library

This library provides replacements for the DBAL types `datetime`, 
`datetime_immutable`, `date`, `date_immutable`, `time`, `time_immutable`. 

The types `datetime` and `datetime_immutable` will automatically 
convert dates into the database timezone and back to the PHP timezone. 

The types `date` and `time` (and the immutable variants) will be cast 
to the database timezone. This means that a time 8:45 will stay 8:45, 
even though this might actually change the date. 


### How to use

Symfony:

```yaml
doctrine:
  dbal:
    # ...
    types:
      datetime: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateTimeType
      datetime_immutable: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateTimeImmutableType
      date: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateType
      date_immutable: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateImmutableType
      time: TS\DoctrineExtensions\DBAL\FixedDbTimezone\TimeType
      time_immutable: TS\DoctrineExtensions\DBAL\FixedDbTimezone\TimeImmutableType
```


To set the database timezone, define a constant 
`define('DATABASE_TIMEZONE', 'Europe/Berline');` or 
set an environment variable `DATABASE_TIMEZONE`.

Otherwise, the PHP timezone is used.


### Background

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/working-with-datetime.html#handling-different-timezones-with-the-datetime-type
