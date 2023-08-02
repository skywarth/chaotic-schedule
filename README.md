# Chaotic Schedule

It's a laravel package in development.


## RNGs

- Mersenne Twister
- https://github.com/paragonie/seedspring
- https://github.com/CiaccoDavide/CiaccoPRNG (*)
- https://github.com/hxtree/pseudorandom (*)

## TODOs

- [ ] Time based macros
  - [X] Random for `at('15:30')`. Exact singular time.
  - [ ] Random for `hourlyAt(17)`
  - [ ] Random for `dailyAt('13:00')`
  - [ ] Random for `twiceDaily(1, 13, 15)` 
  - [ ] Random for `twiceDailyAt(1, 13, 15)` 
  - [ ] Random for **custom** `everyRandomMinutes()`
  - 