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
and `datetime_immutable`, which will automatically convert dates 
into the database timezone. 

When reading a `datetime` or `datetime_immutable`, the timezone 
will **not be converted to the PHP timezone**. The dates will refer 
to the correct point in time, but have the database timezone. 


### Regarding standalone date and time

A date without a time, or a time without a date both do not refer 
to an absolute point in time. Since PHP does not have types to 
represent stand-alone times or dates, developers use `DateTime` 
objects to represent those too, but their timezone should be ignored.

Therefore, it should be fine to use the standard `date` and `time` 
doctrine types. 


### How to use

Symfony:

```yaml
doctrine:
  dbal:
    # ...
    types:
      datetime: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateTimeType
      datetime_immutable: TS\DoctrineExtensions\DBAL\FixedDbTimezone\DateTimeImmutableType
```


To set the database timezone, define a constant 
`define('DATABASE_TIMEZONE', 'Europe/Berline');` or 
set an environment variable `DATABASE_TIMEZONE`.

Otherwise, the PHP timezone is used.


### Background

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/working-with-datetime.html#handling-different-timezones-with-the-datetime-type
