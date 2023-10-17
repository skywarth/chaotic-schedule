# Chaotic Schedule

Laravel package for randomizing command schedule intervals via pRNGs.

[![codecov](https://codecov.io/gh/skywarth/chaotic-schedule/graph/badge.svg?token=GNSG586LG2)](https://codecov.io/gh/skywarth/chaotic-schedule)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=skywarth_chaotic-schedule&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=skywarth_chaotic-schedule)
[![DeepSource](https://app.deepsource.com/gh/skywarth/chaotic-schedule.svg/?label=active+issues&show_trend=true&token=klpu6ClKPxNZm4A8fTPx8fJU)](https://app.deepsource.com/gh/skywarth/chaotic-schedule/?ref=repository-badge)
[![DeepSource](https://app.deepsource.com/gh/skywarth/chaotic-schedule.svg/?label=resolved+issues&show_trend=true&token=klpu6ClKPxNZm4A8fTPx8fJU)](https://app.deepsource.com/gh/skywarth/chaotic-schedule/?ref=repository-badge)

Packagist: https://packagist.org/packages/skywarth/chaotic-schedule

## Table of Contents

- [Installation](#installation)
- [Problem Definition](#problem-definition)
  - [Use Cases](#use-cases)
- [Documentation](#documentation)
  - [How to Use](#how-to-use)
    - [Random Time](#random-time)
    - [Random Date](#random-date)
  - [Info for Nerds](#info-for-nerds)
- [Roadmap & TODOs](#roadmap-and-todos)
- [Credits & References](#credits-and-references)



<a name='installation'></a>
## Installation

1. Install the package via composer:
```bash
composer require skywarth/chaotic-schedule
```

2. `(optional)` Publish the config in order to customize it
```bash
 php artisan vendor:publish --provider "Skywarth\ChaoticSchedule\Providers\ChaoticScheduleServiceProvider" --tag="config"
```

3. Done. You may now use random time and date macros on schedules


<a name='problem-definition'></a>
##  Problem Definition

Ever wanted to run your scheduled commands on random times of the day, or on certain days of the week? Or you may need to send some notifications not on fixed date times, but rather on random intervals hence it feels more *human*. Then this is the package you're looking for.

This Laravel packages enables you to run commands on random intervals and periods while respecting the boundaries set exclusively by you.


<a name='use-cases'></a>
### Use Cases

- I have a command to send notifications to my clients. But I would like it to be sent at a random time between `14:00` and `17:00`
- I would like to send some gifts to users if they are active between my special event period which is every week `Friday` and `Saturday` between `00:00` and `04:20`
- My boss asked me to generate and send statistical reports regarding database activities every month, but only on `Monday`, `Wednesday` and `Friday`. And this report has to be delivered in the morning between `08:00` and `09:30` and I want it to look like I've personally generated and sent it personally. So random time and date is crucial to stage this.
- I would like to send reminders to customers and I want it to look and feel *human*. So random run times and dates every week would help me a lot. Otherwise, if I send every week on `Tuesday` `11:00` they would know this is automated and ignore these. 
- I'm trying to detect certain anomalies in my data, and therefore it would help me a lot to run a command completely randomly but with a minimum of at least 100 times a year.

<a name='documentation'></a>
## Documentation




<a name='roadmap-and-todos'></a>
## Roadmap & TODOs

- [X] Problem: These damned PRNG doesn't work well with massive seed values.
  - [X] Abstract class for RNG adapters to enforce seed format (size, type, etc.)~~
  - [X] New hashing solution for steady flow of seeds (on SeedGenerationService).
    - [X] Every method in the service should pass through hashing, `intval` on its own is just poor.
- [X] ~~Timezone adaptation, we should utilize timezone macro.~~ (Canceled. Not needed. Laravel handles it)
- [ ] Closure based schedules, those that do not have a command.
- [X] Time based macros
  - [X] Random for `->at('15:30')`. Exact singular time.
  - [X] Random for `->hourlyAt(17)`
  - [X] Random for `->dailyAt('13:00')` 
  - [ ] (Skip. Not really necessary) ~~Random for `->twiceDailyAt(1, 13, 15)`~~ 
  - [ ] (Not feasible. What are we going to bind/anchor our seed on ?) ~~Random for **custom** `everyRandomMinutes()`~~
  - [ ] [!] Seeds should be expanded and distinguished.
      Example case: `->days(Schedule::MONDAY,Schedule::FRIDAY,Schedule::SATURDAY)->atRandom('09:00','22:44')`. Otherwise, it doesn't display next due date correctly. Not really a bug but incorrect in terms of informing the user.
      Config for this might be better. `->nextRunDate()` acts up for the `->daily()`.
  - [X] Closure parameters for adjustments and flexibility
  - [X] Determine and indicate boundary inclusivity
- [X] Date based macros
  - [X] (Changed a bit) Create an array of the designated days of the week to be selected from.
    Shuffle this array using RNG.
    Based on the requirement (like 2 times a week or 6 times a month), slice the array to get the required number of days.
    Return the selected days.
  - It should enable the following scenarios (times in the following only define date/day based times! It doesn't take time/minute into account.)
    - [X] Once a week, any day of the week
    - [X] Once a week, among wednesday and friday
      - Example: It'll run wednesday this week. (Basically you roll dice each week)
    - [X] Twice a week, among thursday, saturday and monday.
      - Example: It'll run thursday and monday this week
      - Example: It'll run saturday and monday this week
    - [X] 1-3 times this week, on tuesday, wednesday and friday **(need validation so that the times doesn't exceed max possible days)**
      - Example: this week it'll run 2 times, on tuesday and wednesday
      - Example: this week it'll run once, on friday.
    - [X] 2-7 times this week, on any day of the week
      - Example: this week it'll run 5 times on [...]
    - [X] Once a month, on any day of the week
    - [X] 4 times a month, on only odd number of the day of the month (3,7,17,23)
    - [X] 10-20 times a month, on monday, wednesday, thursday and saturday
    - [X] 30 times a year, on monday and wednesday.
    - [X] This one is not easy: 10 times a year, on saturday and sunday, runs should have a buffer span of at least 4 weeks. So it would run at the minimum per 4 weeks.
  - [X] So here's the gatherings so far, analysis:
    - [X] `period` context. Week, month, year...
    - [X] Constraints and limitations: `days of the week` (separate param), `buffer` (Separate param? .what should be the minimum diff between designated runs) ,others (such as running only on odd number days) can be handled via closures hopefully  
    - [X] There is `times`, defining how many times it should run for the given `period`. It is not related at all with random time schedules.
    - [X] `times` should be validated to not exceed max possible runs for the given `period` and constraints (day of the weeks etc)
- [ ] Indicating next runs dates. Either via overriding `schedule:list` or defining a custom command which works specifically for commands that use our macros.
  - [ ] Mark the commands that use our macros.
- [X] CI/CD pipeline (build, run tests, maybe auto publish?)
- [ ] PHPDoc comments for methods and classes
- [X] Unit/feature tests
  - [X] Time based methods and macros
    - [X] Macro registration assertion
    - [X] Consistency of generated randoms based on seed
    - [X] Unique identifier (parameter and auto)
    - [X] Invalid params (out of range, min-max order, format)
    - [X] Boundaries are respected (min-max values, does the generated time exceed these limits ?)
      - [X] On RNGAdapter
      - [X] On macros
    - [X] Closures
  - [X] Date based methods and macros
    - [X] Macro registration assertion
    - [X] Consistency of generated randoms based on seed
    - [X] Unique identifier (parameter and auto)
    - [X] Invalid params (out of range, min-max order, format)
    - [X] Closures

<a name='credits-and-references'></a>
## Credits & References

### RNGs

- [Mersenne Twister](https://github.com/ruafozy/php-mersenne-twister)
- [Seed Spring](https://github.com/paragonie/seedspring)
- https://github.com/CiaccoDavide/CiaccoPRNG (Not Implemented)
- https://github.com/hxtree/pseudorandom (Not Implemented)

