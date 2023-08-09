# Chaotic Schedule

It's a laravel package in development.


## Installation (WIP)

```php
 php artisan vendor:publish --provider "Skywarth\ChaoticSchedule\Providers\ChaoticScheduleServiceProvider" --tag="config"
```

## RNGs

- Mersenne Twister
- https://github.com/paragonie/seedspring
- https://github.com/CiaccoDavide/CiaccoPRNG (*)
- https://github.com/hxtree/pseudorandom (*)

## TODOs

- [ ] Problem: These damned PRNG doesn't work well with massive seed values.
  - [ ] Abstract class for RNG adapters to enforce seed format (size, type, etc.)
  - [ ] New hashing solution for steady flow of seeds (on SeedGenerationService).
    - [ ] Every method in the service should pass through hashing, `intval` is just poor.
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
- [ ] Date based macros
  - [ ] Random for `->days(Schedule::MONDAY,Schedule::WEDNESDAY,Schedule::FRIDAY)` 
  - [ ] Random for `->weeklyOn(1, '8:00');`
  - [ ] Random for `->monthlyOn(1, '8:00');`
- [ ] CI/CD pipeline (build, run tests, maybe auto publish?)
- [ ] Unit tests
  - [ ] Time based methods and macros
    - [ ] Macro registration assertion
    - [ ] Consistency of generated randoms based on seed
    - [ ] Unique identifier (parameter and auto based on date)
    - [ ] Invalid params (out of range, min-max order, format)