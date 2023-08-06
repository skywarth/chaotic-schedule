# Chaotic Schedule

It's a laravel package in development.


## RNGs

- Mersenne Twister
- https://github.com/paragonie/seedspring
- https://github.com/CiaccoDavide/CiaccoPRNG (*)
- https://github.com/hxtree/pseudorandom (*)

## TODOs

- [X] [!] ~~Timezone adaptation, we should utilize timezone macro.~~ (Canceled. Not needed. Laravel handles it)
- [ ] Time based macros
  - [X] Random for `->at('15:30')`. Exact singular time.
  - [X] Random for `->hourlyAt(17)`
  - [X] Random for `->dailyAt('13:00')` 
  - [ ] (Skip maybe?) Random for `->twiceDailyAt(1, 13, 15)` 
  - [ ] Random for **custom** `everyRandomMinutes()`
- [ ] Date based macros
  - [ ] Random for `->days(Schedule::MONDAY,Schedule::WEDNESDAY,Schedule::FRIDAY)` 
  - [ ] Random for `->weeklyOn(1, '8:00');`
  - [ ] Random for `->monthlyOn(1, '8:00');`
- CI/CD pipeline (build, run tests, maybe auto publish?)
- Unit tests