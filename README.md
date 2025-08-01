# Introduction

PEAR MDB2 is a project to merge PEAR DB and Metabase into one DB
abstraction layer.

For mor information about these projects, see:

- [PEAR DB](https://pear.php.net/package/DB)
- [Metabase](http://phpclasses.upperdesign.com/browse.html/package/20/)

At these URLs you will also find the licensing information on these two
projects along with the credits.

Actually MDB2 is the second major version of MDB. The main differences between
the new MDB2 and the old MDB version is that the API has been drastically
refactored to clean up the API and improve performance.

If you have any questions or suggestions you can contact
[Lukas Smith](mailto:smith@backendmedia.com). The project's co-author is
[Christopher Linn](mailto:clinn@backendmedia.com).

Or even better post a message to the
[pear-dev@lists.php.net](mailto:pear-dev@lists.php.net) mailinglist for
development related questions of the MDB2 package itself. For questions
using MDB2 pelase direct your questions to
[pear-general@lists.php.net](mailto:pear-general@lists.php.net).

# Features

MDB2 provides a common API for all support RDBMS. The main difference to most
other DB abstraction packages is that MDB2 goes much further to ensure
portability. Among other things MDB2 features:

- An OO-style query API
- A DSN (data source name) or array format for specifying database servers
- Datatype abstraction and on demand datatype conversion
- Portable error codes
- Sequential and non sequential row fetching as well as bulk fetching
- Ordered array and associative array for the fetched rows
- Buffered and Unbuffered fetching
- Prepare/execute (bind) emulation
- Sequence emulation
- Replace emulation
- Limited Subselect emulation
- Row limit support
- Transactions support
- Large Object support
- Index/Unique support
- Extension Framework to load advanced functionality on demand
- Table information interface
- RDBMS management methods (creating, dropping, altering)
- RDBMS independent xml based schema definition management
- Altering of a DB from a changed xml schema
- Reverse engineering of xml schemas from an existing DB (currently MySQL and PgSQl)
- Full integration into the PEAR Framework
- PHPDoc API documentation

# Getting Started

I would first recommend taking a look at `docs/example.php`. This should give
you a general feel of how to interact with MDB2.

After that you may want to
[take a look at the API](https://pear.php.net/package/MDB2/docs/latest/). There
you will also find a document describing the XML schema format and a little
tutorial (it was just recently ported from Metabase, so it may contain errors).

# Package Content

As a user the only PHP script you will need to include is `MDB2.php` which will
install to your PEAR root directory. All other files and their containing
classes will be included via `MDB2::factory()`, `MDB2::connect()`, and
`MDB2::singleton()`.

These will load additional classes. Most classes are loaded on demand.

Furthermore MDB2 provides an extensive testing framework that works through a
browser and command line interface. There are several other test that test the
two wrappers. These files will install into your test dir found in the
PEAR root dir.

# Documentation

- [End-user documentation](http://pear.php.net/manual/en/package.database.mdb2.php)
- [API documentation](https://pear.php.net/package/MDB2/docs/latest/)

The entire public API and most of the private methods (except for some of the
large object classes) have been documented with PHPDoc comments. Most of the
API is borrowed from ext-pdo, so you can look there for detailed documentation.

# Testing

For most of the tests you can set the username/password/hostname in the
relevant config file. The user will need to have the right to create new
databases.

`test.php/clitest.php/testchoose.php` is the native testing suite provided by
MDB2. Please see the README in the tests directory for details.

`example.php` contains several test calls to MDB2's native API. It requires
the `PEAR::VAR_Dump` package and is configured to use the following settings:

```
username = metapear
password = funky
hostname = localhost
```

# How to Write New Drivers

Skeleton drivers are provided in the docs directory of the MDB2 package.

The best course of action would be to take a MDB2 driver and hack it to fit
the new RDBMS. This will surely be faster and it will ensure that the new
driver takes full advantage of the MDB2 framework. I would however recommend
working with the existing Metabase driver for inspiration that RDBMS when
doing those changes.

In order to check compliance of the driver with MDB2 you can use the testing
suite (see the _testing_ section above)

# History

MDB was started after Manuel broad be into the discussion about getting the
features of Metabase into PEAR that was going on (again) in December 2001. He
suggested that I could take on this project. After alot of discussion about
how when and if etc I started development in February 2002.

MDB2 is based on Metabase but has been reworked severely to better match
the PEAR DB API and PEAR CS. The approach I have taken so far is to take
`DB.php` from PEAR DB and make it create a Metabase object. I have changed the
Metabase structure slightly. The formatting has been reworked
considerably to better fit the final structure (MDB standalone with a
BC-wrapper for Metabase and PEAR DB), to fit the PEAR CS and to make it
easier for other people to contribute.

The `metabase_interface.php` was been renamed to `metabase_wrapper.php` and
now only serves as a wrapper to keep BC with Metabase. A wrapper will
have to be created for PEAR DB as well.

Basically the result is a Metabase that is really close to the PEAR DB
structure. I have also added any missing methods from PEAR DB. Work on
moving the error handling to PEAR error handling is under way but still
needs some work.

In MDB2 the API was heavily refactored to be even more streamlined. Redundant
features have been removed. Some features where moved out of the core into
separate loadable modules. Instead of resources resultsets are now wrapped
into objects similar to PEAR DB.

# Credits

I would especially like to thank Manuel Lemos (Author of Metabase) for
getting me involved in this and generally being around to ask questions.
I would also like to thank Tomas Cox and Stig S. Bakken from the PEAR
projects for help in undertstanding PEAR, solving problems and trusting
me enough. Paul Cooper for the work on the pgsql driver. Furthermore I
would like to thank Alex Black for being so enthusiastic about this
project and offering binarycloud as a test bed for this project.
Christian Dickmann for being the first to put MDB to some real use,
making MDB use PEAR Error and working on the XML schema manager.

Finally Peter Bowyer for starting the discussion that made people pick
up this project again after the first versions of what was then called
"metapear" have been ideling without much feedback. I guess I should
also thank BackendMedia (my company :-) ) for providing the necessary means
to develop this on company time (actually for the most part my entire
life is company time ... so it goes)
