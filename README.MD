# Template
Basic CSS development kit based on Bootstrap 4.

## Usage
This packages consists only of classes that represent single (rarely - multiple) CSS properties. For example class `fs-2` represent `font-size: 2rem` property, `pb-0.3` - `padding-bottom: 0.3rem`, `f-l` - `float: left` and so on. It is the name convention of class name, where each letter represents a whole word, multiple letters - multiple words. Almost every class has responsible BS4 breakpoints. All sizes use `rem` CSS unit, the root element has font-size of 10px by default.

The package is divided into modules, each can be compiled separately, just enable/disable them in [_modules.scss](css/_modules.scss) and run `npm run sass`.

Some property names have name collisions. This is dome by adding additional letters to problematic class name, i.e. properties `font-size` and `flex-shrink` both have the same shorthand CSS class name: `fs`, so the last one is renamed to `fsh` and so on.
