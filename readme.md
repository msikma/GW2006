[![MIT license](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://opensource.org/licenses/MIT)

# GW2006

A theme for [SMF 2.0.19](https://www.simplemachines.org/) (Simple Machine Forums) written mostly in [Twig](https://twig.symfony.com/) templates.

Gaming World is a website and forum that has existed since the year 2000. Over the years it migrated from one forum software to another, but for the longest period it was on SMF 1.0 and then 2.0, starting from 2006.

This is a modern recreation of the 2006 version of its layout, with the goal being to keep it close to the original design's spirit while still adding polish and updates all around.

## Development

Dependencies must be installed through [Composer](https://getcomposer.org/):

```sh
composer install
```

The stylesheet is written in SCSS, so [Sass](https://sass-lang.com/) must be installed. To develop styles, run `composer run dev-scss`.

This theme is built using [Twig](https://twig.symfony.com/) templates. 

## Migration

One of the major tasks for this project was the migration of old data to SMF 2.0. Prior to the move, the forum was running on [IPB 3.4.1](https://invisioncommunity.com/). While a converter was available, it needed some changes to correctly convert the existing data.

Due to the forum having been moved around to different forum systems several times, some of the old data has gotten lost over time. Not just old posts, but also a lot of metadata such as user avatars, custom user groups, and various other things. Since Gaming World has a significant amount of pages archived on the [Wayback Machine](https://archive.org/), we wrote a scraper to extract some of this data from the archive and reinsert it into the database. **[Todo: writeup of this process.]**

## Links

* [Gaming World](https://gamingw.net/)
* [Salt World](https://saltworld.net/) (2010-2022)
* [Simple Machines Forum homepage](https://www.simplemachines.org/)
    * [Online manual](https://wiki.simplemachines.org/)
* [SVGGraph](https://www.goat1000.com/svggraph.php) - Used for creating SVG graphs

## License

MIT license.

© 2000, Bart for the original Gaming World website and concept;

© 2006, leafo and ramirez for the original layout that this is based on;

© 2022, dada78641 for this repository.
