# Chaotic Schedule

Laravel package for randomizing command schedule intervals via pRNGs.


[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=skywarth_chaotic-schedule&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=skywarth_chaotic-schedule)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=skywarth_chaotic-schedule&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=skywarth_chaotic-schedule)
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=skywarth_chaotic-schedule&metric=duplicated_lines_density)](https://sonarcloud.io/summary/new_code?id=skywarth_chaotic-schedule)


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
    - [Random Time Macros](#random-time-macros)
      - [atRandom](#at-random)
      - [dailyAtRandom](#daily-at-random)
      - [hourlyAtRandom](#hourly-at-random)
      - [hourlyMultipleAtRandom](#hourly-multiple-at-random)
    - [Random Date Macros](#random-date-macros)
  - [Info for Nerds](#info-for-nerds)
    - [Consistency, seed and pRNG](#consistency-seed-prng)
    - [Asserting the chaos](#asserting-the-chaos)
    - [Performance](#performance)
- [Roadmap & TODOs](#roadmap-and-todos)
- [Credits & References](#credits-and-references)



<a name='installation'></a>
## Installation

0. Consider the requirements
   - PHP >=`7.4` is required


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
- There is a financial deficit, in order to detect the source of it I'll be running audit calculations. But these have to be random, otherwise they'll alter the records accordingly. I need to run audit calculations/assertions 3 times a day at random times.
- I'm trying to detect certain anomalies in my data, and therefore it would help me a lot to run a command completely randomly but with a minimum of at least 100 times a year.

<a name='documentation'></a>
## Documentation

<a name='how-to-use'></a>

<a name='random-time-macros'></a>
### Random Time Macros


<a name='at-random'></a>
#### 1. `->atRandom(string $minTime, string $maxTime,?string $uniqueIdentifier=null,?callable $closure=null)`
Used for scheduling your commands to run at random time of the day. 

- Only designates random **run time**
- Doesn't designate any date on the schedule. So you may have to provide some date scheduling such as `daily()`, `weekly()`, `mondays()`, `randomDays()` etc.

| Parameter          | Type                | Example Value                                                   | Description                                                                                                                                                                                                                                                                                          |
|--------------------|---------------------|-----------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| `minTime`          | string              | `'14:15'`                                                       | Minimum value for the random time range (inclusive)                                                                                                                                                                                                                                                  |
| `maxTime`          | string              | `'22:38'`                                                       | Maximum value for the random time range (inclusive)                                                                                                                                                                                                                                                  |
| `uniqueIdentifier` | string (nullable)   | `'my-custom-identifier'`                                        | Custom identifier that will be used for determining seed for the given command. If null/default provided, command's signature will be used for this. **It is primarily used for distinguishing randomization of same command schedules.**                                                            |
| `closure`          | callable (nullable) | <pre>function(int $motd){<br><br>return $motd+5;<br>}<br></pre> | Optional closure to tweak the designated random minute of the day according to your needs. For example you may use this to run the command only on odd-numbered minutes. `int` minute of the day and `Event` (Schedule) instance is injected, meanwhile `int` response is expected from the closure. |

- ##### Example usage #1

Run a command daily on a random time between 08:15 and 11:42
```php
$schedule->command('your-command-signature:here')->daily()->atRandom('08:15','11:42');
```

- ##### Example usage #2

Run a command every Tuesday, Saturday and Sunday on a random time between 04:20 and 06:09
```php
$schedule->command('your-command-signature:here')->days([Schedule::TUESDAY, Schedule::SATURDAY, Schedule::SUNDAY])->atRandom('04:20','06:09');
```

- ##### Example usage #3

Run a command every Sunday between 16:00 - 17:00 and also on Monday between 09:00 - 12:00

**Notice the unique identifier parameter**
```php
//Observe that the both schedules share the same command, but one has custom unique identifier
$schedule->command('your-command-signature:here')->sundays()->atRandom('16:00','17:00');
$schedule->command('your-command-signature:here')->mondays()->atRandom('09:00','12:00','this-is-special');
//Since the latter has a unique identifier, it has a distinguished seed which completely differentiates the generated randoms.
```

- ##### Example usage #4
Run a command weekdays at a random time between 12:00 and 20:00, but only if the hour is not 15:00.

```php
$schedule->command('your-command-signature:here')->weekdays()->atRandom('16:00', '17:00', null, function(int $motd){
  if($motd>=900 && $motd<=960){//$motd represents minute-of-the-day. 900th minute is 15:00. 
    return $motd+60;
  }else{
    return $motd;     
  }
});
```

<a name='daily-at-random'></a>
#### 2. `->dailyAtRandom(string $minTime, string $maxTime,?string $uniqueIdentifier=null,?callable $closure=null)`

Identical to [atRandom](#at-random) macro. Just a different name.


<a name='hourly-at-random'></a>
#### 3. `->hourlyAtRandom(int $minMinutes=0, int $maxMinutes=59,?string $uniqueIdentifier=null,?callable $closure=null)`

Used for scheduling you commands to run every hour at random minutes.

- Runs every hour, but at random minutes for each hour
- Only designates random **run time**
- Doesn't designate any date on the schedule. So you may have to provide some date scheduling such as `daily()`, `weekly()`, `mondays()` etc.



| Parameter          | Type                | Example Value                                                                                     | Description                                                                                                                                                                                                                                                                                                                              |
|--------------------|---------------------|---------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| `minMinutes`       | int                 | `15`                                                                                              | Minimum value for the random minute of hour (inclusive)                                                                                                                                                                                                                                                                                  |
| `maxMinutes`       | int                 | `44`                                                                                              | Maximum value for the random minute of hour (inclusive)                                                                                                                                                                                                                                                                                  |
| `uniqueIdentifier` | string (nullable)   | `'my-custom-identifier'`                                                                          | Custom identifier that will be used for determining seed for the given command. If null/default provided, command's signature will be used for this. **It is primarily used for distinguishing randomization of same command schedules.**                                                                                                |
| `closure`          | callable (nullable) | <pre>function(int $randomMinute, Event $schedule){<br><br>return $randomMinute%10;<br>}<br></pre> | Optional closure to tweak the designated random minute according to your needs. For example you may use this to run the command only on multiplies of 10. <br><br> Generated `int` random minute (between 0-59) and `Event` (Schedule) instance is injected, meanwhile `int` response that is between 0-59 is expected from the closure. |

- ##### Example usage #1

Run a command every hour between 15th and 25th minutes randomly.

```php
$schedule->command('your-command-signature:here')->hourlyAtRandom(15,25);
```

- ##### Example usage #2

Run a command every hour twice, once between 0-12 minute mark, another between 48-59 minute mark.

```php
$schedule->command('your-command-signature:here')->hourlyAtRandom(0,12);
$schedule->command('your-command-signature:here')->hourlyAtRandom(48,59,'custom-identifier-to-customize-seed');
```

- ##### Example usage #3

Run a command every hour, between minutes 30-45 but only on multiplies of 5.

```php
$schedule->command('your-command-signature:here')->hourlyAtRandom(30,45,null,function(int $minute, Event $schedule){
return min(($minute%5),0);
});

```

<a name='hourly-multiple-at-random'></a>
#### 4. `->hourlyMultipleAtRandom(int $minMinutes=0, int $maxMinutes=59, int $timesMin=1, int $timesMax=1, ?string $uniqueIdentifier=null,?callable $closure=null)

Similar to [->hourlyAtRandom](#hourly-at-random), it is used for scheduling your commands to run every hour on random minutes. 
Difference between this and `->hourlyAtRandom` is: `->hourlyMultipleAtRandom` allows you to run command multiple times per hour.

Example use case: I want to run a command every hour, 1-5 times at random, on random minutes. E.g. run minutes:[5,11,32,44]

- Runs every hour
- Only designates random **run time(s)**
- Runs multiple times per hour, according to `$timesMin` and `$timesMax` params
- [!] Using it along with `->everySixHours()` or similar methods are **NOT RECOMMENDED**, because those are also time scheduling methods, and they'll overwrite each other.
- Doesn't designate any date on the schedule. So you may have to provide some date scheduling such as `daily()`, `weekly()`, `mondays()` etc.



| Parameter          | Type                | Example Value                                                                                                                  | Description                                                                                                                                                                                                                                                                                                                                                                                                                                |
|--------------------|---------------------|--------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| `minMinutes`       | int                 | `15`                                                                                                                           | Minimum value for the random minute of hour (inclusive)                                                                                                                                                                                                                                                                                                                                                                                    |
| `maxMinutes`       | int                 | `44`                                                                                                                           | Maximum value for the random minute of hour (inclusive)                                                                                                                                                                                                                                                                                                                                                                                    |
| `timesMin`         | int                 | `3`                                                                                                                            | Minimum amount of times to run this command per hour (inclusive). E.g: `$timesMin=3`, `$timesMax=10`, the command will run at least 3, at the most 10 times per hour. Run amounts decided per hour basis.                                                                                                                                                                                                                                  |
| `timesMax`         | int                 | `10`                                                                                                                           | Maximum amount of times to run this command per hour (inclusive). E.g: `$timesMin=5`, `$timesMax=17`, the command will run at least 5, at the most 17 times per hour. Run amounts decided per hour basis.                                                                                                                                                                                                                                  |
| `uniqueIdentifier` | string (nullable)   | `'my-custom-identifier'`                                                                                                       | Custom identifier that will be used for determining seed for the given command. If null/default provided, command's signature will be used for this. **It is primarily used for distinguishing randomization/seeding of same command schedules.**                                                                                                                                                                                          |
| `closure`          | callable (nullable) | <pre>function (Collection $minutes,Event $event){<br><br> return $minutes->diff([4,8,15,16,23,42])->values();<br> };<br></pre> | Optional closure to tweak the designated random run minutes according to your needs. For example you may use this to run the command only on those minutes which are not in an array. <br><br> Designated random run minutes `Collection` that consist of `int` minutes (between 0-59) and `Event` (Schedule) instance is injected, meanwhile `Collection` response that contains `int` minutes between 0-59 is expected from the closure. |

- ##### Example usage #1

Run a command 4-5 (random) times per hour, only on weekdays (constant, every day), between 08:00 and 18:00 (constant, every hour between these). Minutes of each hour are random.

>https://www.reddit.com/r/laravel/comments/18v714l/comment/ktkyc72/?utm_source=share&utm_medium=web2x&context=3

```php
$schedule->command('your-command-signature:here')->hourlyMultipleAtRandom(0,59,4,5)->weekdays()->between('08:00','18:00');
```


- ##### Example usage #2

Run a command exactly 8 times an hour, it should run only between 20-40 minute marks, run only on wednesdays.

```php
$schedule->command('your-command-signature:here')->hourlyMultipleAtRandom(20,40,8,8)->wednesdays();
```

- ##### Example usage #3

Run a command 2-6 times an hour, it should run only between 10-48 minute marks, run only on; tuesday,thursday,saturday, it should run only on even(divisible by 2) minutes.

```php
$schedule->command('your-command-signature:here')->hourlyMultipleAtRandom(10,48,2,6,null,function(Collection $designatedMinutes,Event $event){
    return $designatedMinutes->map(function(int $minute){
        return $minute-(($minute%2));//rounding numbers to closest even number, if the number is odd
    });
})->days([Schedule::TUESDAY, Schedule::THURSDAY,Schedule::SATURDAY])();
```

---

<a name='random-date-macros'></a>
### Random Date Macros

#### 1. `->randomDays(int $periodType, ?array $daysOfTheWeek, int $timesMin, int $timesMax, ?string $uniqueIdentifier=null,?callable $closure=null)`

Used for scheduling your commands to run at random dates for given constraints and period.

- Only designates random **run date**
- Doesn't designate any run time on the schedule. So you **have to** provide some time scheduling such as `hourly()`, `everySixHours()`, `everyTenMinutes()`, `atRandom()` etc.


| Parameter          | Type                  | Example Value                                                                                                                                                                                                   | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
|--------------------|-----------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| `periodType`       | int                   | `RandomDateScheduleBasis::Week`                                                                                                                                                                                 | The most crucial parameter for random date scheduling. It defines the period of the random date range, seed basis/consistency and generated random dates. It defines the seed for the random dates, so for the given period your randoms stay consistent. You may use any value presented in `RandomDateScheduleBasis` class/enum.                                                                                                                                                                                                                          |
| `daysOfWeek`       | array<int> (nullable) | `[Carbon::Sunday, Carbon::Tuesday]`                                                                                                                                                                             | Days of the week that will be used for random date generation. Only those days you pass will be picked and used. For example: if you pass `[Carbon::Wednesday, Carbon:: Monday]`, random dates will be only on wednesdays and mondays. Since it is optional, if you don't pass anything for it that means all days of the week will be available to be used.                                                                                                                                                                                                |
| `timesMin`         | int                   | `2`                                                                                                                                                                                                             | Defines the minimum amount of times the command is expected to run for the given period. E.g: period is `week` and `timesMin=4`, that means this command will run at least 4 times each week.                                                                                                                                                                                                                                                                                                                                                               |
| `timesMax`         | int                   | `12`                                                                                                                                                                                                            | Defines the maximum amount of times the command is expected to run for the given period. E.g: period is `month` and `timesMin=5` and `timesMax=12`, that means this command will run at least 5, at most 12 times each month. Exact number of times that it'll run is resolved in runtime according to seed.                                                                                                                                                                                                                                                |
| `uniqueIdentifier` | string (nullable)     | `'my-custom-identifier'`                                                                                                                                                                                        | Custom identifier that will be used for determining seed for the given command. If null/default provided, command's signature will be used for this. **It is primarily used for distinguishing randomization of same command schedules.**                                                                                                                                                                                                                                                                                                                   |
| `closure`          | callable (nullable)   | <pre>function(Collection $possibleDates, Event $schedule){<br><br>return $possibleDates->filter(function (Carbon $date){<br/><br/>     return $date->day%2!==0;//odd numbered days only <br/>});<br>}<br></pre> | Closure parameter for adjusting random dates for the command. <br> This closure is especially useful if you would like to exclude certain dates, or add some dates to the possible dates to choose from. <br><br> Possible dates as `Carbon` instances are injected as `collection` to the closures, these dates represent the pool of possible dates to choose from for random dates, it doesn't represent designated run dates. `Event` (Schedule) instance is injected as well. Closure response is expected to be a `collection` of `Carbon` instances. |

- ##### Example usage #1

Run a command 5 to 10 times/days (as in dates) each month randomly.

```php
$schedule->command('your-command-signature:here')->randomDays(RandomDateScheduleBasis::MONTH,[],5,10);
```

- ##### Example usage #2

Run a command exactly 2 times (as in dates) per week, but only on wednesdays or saturdays.

```php
$schedule->command('your-command-signature:here')->randomDays(RandomDateScheduleBasis::WEEK,[Carbon::WEDNESDAY,Carbon::SATURDAY],2,2);
```

- ##### Example usage #3

Run a command 15-30 times (as in dates) per year, only on Fridays.

```php
$schedule->command('your-command-signature:here')->randomDays(RandomDateScheduleBasis::YEAR,[Carbon::FRIDAY],15,30);
```


- ##### Example usage #4

Run a command 1 to 3 times (as in dates) per month, only on weekends, and only on odd days .

```php
$schedule->command('your-command-signature:here')->randomDays(
    RandomDateScheduleBasis::MONTH,
    [Carbon::SATURDAY,Carbon::SUNDAY],
    1,3,
    null,
    function (Collection $dates){
        return $dates->filter(function (Carbon $date){
            return $date->day%2!==0;//odd numbered days only
        });
    }
);
```


### Joint examples

Examples about using both random time and random date macros together.

- ##### Example usage #1

Run a command 1 to 2 times (as in dates) among Friday, Tuesday, Sunday, and only between 14:48 - 16:54


```php
$schedule->command('your-command-signature:here')->weekly()->randomDays(RandomDateScheduleBasis::WEEK,[Carbon::FRIDAY,Carbon::Tuesday,Carbon::Sunday],1,2)->atRandom('14:48','16:54');
```

<a name='info-for-nerds'></a>
### Info for nerds

<a name='consistency-seed-prng'></a>
#### Consistency, seed and pRNG

It was a concern to generate consistent and same random values for the duration of the given interval. 
This is due to the fact that the Laravel scheduler is triggered via CRON tab every minute. So we needed a solution to generate consistent randoms for each trigger of the scheduler. 
Otherwise, it would simply designate random date/time runs each time it runs, which will result in: commands never running at all (because run designation changes constantly), or commands running more that desired/planned.

In the world of cryptography and statistics, such challenges are tackled via pRNGs (pseudo random number generators). pRNGs as indicated in its name: is a pseudo random number generator which works with seed values and generates randoms determined by that seed, hence the name.
Therefore, pRNGs would allow us to generate consistent and exactly same random values as long as seed remains the same. 
Now the question is what seed shall we pair pRNG with? After some pondering around, I deduced that if I give corresponding date/time of the interval, it would effectively be consistent throughout the interval.
Henceforth, all the randomizing methods and macros work by utilizing `SeedGenerationService` which is responsible for generating seeds based on certain intervals (day, month, week etc.)
This ensures your random run date/times are consistent throughout the interval, enabling consistency.

To those with a keen eye, this might present a possible problem. 
If the seed is paired only with interval, wouldn't that result in identical random date/time runs for separate commands? **Exactly!** 
Because of this, `SeedGenerationService` also takes command signatures into consideration by incorporating `uniqueIdentifier` (which is either command signature or custom identifier) into it's seed designation methods. This way, even if the separate commands have identical random scheduling, they'll have distinct randomization for them thanks to the `uniqueIdentifier` 

<a name='asserting-the-chaos'></a>
#### Asserting the chaos

When dealing with pRNGs, nothing is truly chaotic and random, actually. It's all mathematics and statistics, it's deterministic.
In order to ensure no harm could come from these randoms, I've prepared dozens of unit and feature tests for the different aspects of the library.
From seed generation to generated random consistency, from distribution uniformity to validations, from design pattern implementation to dependency injection, all is well tested and asserted.
See the code coverage reports and CI/CD runs regarding these functional tests.

<a name='performance'></a>
#### Performance

As you might already know, Laravel scheduler runs every minute via CRON tab entry (see: https://laravel.com/docs/10.x/scheduling#running-the-scheduler).
And since `kernel.php` (where you define your schedules) runs every minute, Laravel has to determine whether each command is designated to run at this minute or not.
This is performed by running & checking datetime scheduling assignments and `->when()` statements on your command scheduling.

This library heavily relies on pRNG, seed generation based on date/time and run designations.
Hence, it is no surprise these calculations, randomization (pseudo) and designations are performed each time your `kernel.php` runs, which is every minute as explained on the previous paragraph.
**So yes, if you're on low-specs, it could affect your pipeline. Because these seed determination, run date/time designations, pseudo-random values are determined on each schedule iteration.** Massive amounts (250+) of randomized command schedules could clog your server performance a bit in terms of memory usage. 

But other than that, as the *Jules* from *Pulp Fiction* said:
> "As far as I know, MF is tip-top"  



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
  - [X] It should enable the following scenarios (times in the following only define date/day based times! It doesn't take time/minute into account.)
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
- [ ] Add `randomMultipleMinutesSchedule` to documentation
- [ ] Separate test classes per method/macro basis
- [ ] PHPDoc comments for methods and classes
- [ ] Inject basis dateTime Carbon instance into the closures
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
  - [X] Unified macro usage feature test
  - [ ] Timezone tests
    - [ ] Applying timezone works in conjunction with time macros
    - [ ] Applying timezone works in conjunction with date macros
  - [ ] [CRUCIBLE!] Merge all distributed date-time iteration methods in tests into one
- [X] [Use case from reddit, N1](https://www.reddit.com/r/laravel/comments/18v714l/comment/ktkyc72/?utm_source=share&utm_medium=web2x&context=3)
- [ ] Possible bug: `->dateOfWeek` and `->dateOfWeekIso` differ per monday-sunday diff in start. Check existing assertions.

<a name='credits-and-references'></a>
## Credits & References

### RNGs

- [Mersenne Twister](https://github.com/ruafozy/php-mersenne-twister)
- [Seed Spring](https://github.com/paragonie/seedspring)
- https://github.com/CiaccoDavide/CiaccoPRNG (Not Implemented)
- https://github.com/hxtree/pseudorandom (Not Implemented)


This project has been developed using JetBrains products.
Thanks for their [support for open-source development](https://www.jetbrains.com/community/opensource/#support).


| <img width="150" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg" alt="JetBrains Logo (Main) logo."> | <img width="200" src="https://resources.jetbrains.com/storage/products/company/brand/logos/PhpStorm.svg" alt="PhpStorm logo."> |
|--------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------|
