# Chaotic Schedule

[![codecov](https://codecov.io/gh/skywarth/chaotic-schedule/graph/badge.svg?token=GNSG586LG2)](https://codecov.io/gh/skywarth/chaotic-schedule)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=skywarth_chaotic-schedule&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=skywarth_chaotic-schedule)

It's a laravel package in development.


## Installation (WIP)

```php
 php artisan vendor:publish --provider "Skywarth\ChaoticSchedule\Providers\ChaoticScheduleServiceProvider" --tag="config"
```

## RNGs

- [Mersenne Twister](https://github.com/ruafozy/php-mersenne-twister)
- [Seed Spring](https://github.com/paragonie/seedspring)
- https://github.com/CiaccoDavide/CiaccoPRNG (*)
- https://github.com/hxtree/pseudorandom (*)

## TODOs

- [ ] PHPDoc comments for methods and classes
- [X] ~~Problem: These damned PRNG doesn't work well with massive seed values.~~
  - [X] ~~Abstract class for RNG adapters to enforce seed format (size, type, etc.)~~
  - [X] ~~New hashing solution for steady flow of seeds (on SeedGenerationService).~~
    - [X] ~~Every method in the service should pass through hashing, `intval` on its own is just poor.~~
- [X] [!] ~~Timezone adaptation, we should utilize timezone macro.~~ (Canceled. Not needed. Laravel handles it)
- [X] Time based macros
  - [X] Random for `->at('15:30')`. Exact singular time.
  - [X] Random for `->hourlyAt(17)`
  - [X] Random for `->dailyAt('13:00')` 
  - [ ] (Skip. Not really necessary) ~~Random for `->twiceDailyAt(1, 13, 15)`~~ 
  - [ ] (Not feasible. What are we going to bind/anchor our seed on ?) ~~Random for **custom** `everyRandomMinutes()`~~
  - [ ] [!] Seeds should be expanded and distinguished.
      Example case: `->days(Schedule::MONDAY,Schedule::FRIDAY,Schedule::SATURDAY)->atRandom('09:00','22:44')`. Otherwise, it doesn't display next due date correctly. Not really a bug but incorrect in terms of informing the user.
      Config for this might be better. `->nextRunDate()` acts up for the `->daily()`.
  - [ ] Closure parameters for adjustments and flexibility
  - [X] Determine and indicate boundary inclusivity
- [ ] Date based macros
  - [ ] Random for `->days(Schedule::MONDAY,Schedule::WEDNESDAY,Schedule::FRIDAY)` 
  - [ ] Random for `->weeklyOn(1, '8:00');`
  - [ ] Random for `->monthlyOn(1, '8:00');`
- [ ] CI/CD pipeline (build, run tests, maybe auto publish?)
- [ ] Unit tests
  - [ ] Time based methods and macros
    - [X] Macro registration assertion
    - [ ] Consistency of generated randoms based on seed
    - [X] Unique identifier (parameter and auto)
    - [X] Invalid params (out of range, min-max order, format)
    - [ ] Boundaries are respected (min-max values, does the generated time exceed these limits ?)
      - [ ] On RNGAdapter
      - [X] On macros

