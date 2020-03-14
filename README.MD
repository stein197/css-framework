# Template

Basic CSS and JS development kit based on Bootstrap 4. Most of the classes can be applied to any BS4-compatible breakpoint (like classes `.fs-20` and `.fs-md-20`) as well as BS4's classes.

## Usage

Include files contained in `dist` folder to your page.

Note, that if you want build this files by yourselves then you need to install these utilities and add them to PATH variable:
- [uglify-js](https://www.npmjs.com/package/uglify-js) for Node.js
- [sass](https://rubygems.org/gems/sass/versions/3.4.22) gem for Ruby

## Description

All elements on the page have next styles by default:

```CSS
position: relative;
box-sizing: border-box;
background-repeat: no-repeat;
background-position: center;
```

All tables on the page have next styles by default:
```CSS
border-collapse: collapse;
```

## Main classes

| Class | Description |
| --- | --- |
| .float | Prevents element from collapsing when applied to element that has floating children |
| .clear | Clears any float values |
| .circle | Transforms an element to circle form |
| .cp | Applies `cursor: pointer` to an element |
| .nolink | Removes any underlines from links |
| .crop | Crops any overflowed content |
| .bg-default | Places background image of an element to the center and makes its size to cover the element |
| .fixed | When applied to `<header>` element makes it pinned to the top of the page. When applied to `<body>`, then it has the same effect as `.crop` class applied to it. Otherwise does nothing |
| .table-wrapper | Should be applied as parent relative to a `<table>`. If table overflows parent's width, then horizontal scroll appears |
| .btn | Should be applied to buttons and button-like elements so the will have appropriate behaviour like not having whitespace breaks etc. |
| .btn-mobile | When applied to button elements makes them 100% width and centers its content on screen size less than 768px |
| .brd-rd-`<n>` | Sets element's border-radius. Allowable values for `<n>` are ranging from 1 to 20 inclusive |

## Font and text classes

| Class | Description |
| --- | --- |
| .fw-`*` | Controls the font weight from 100 (thin) to 900 (black). Allowable values are: <ul><li>thin (100)</li><li>light-x (200)</li><li>light (300)</li><li>regular (400)</li><li>medium (500)</li><li>bold-s (600)</li><li>bold (700)</li><li>bold-x (800)</li><li>black (900)</li></ul> |
| .fs-`<n>` | Sets element's font size in pixels from 10 to 50 inclusive. Note that all values are even, like `.fs-12`, `.fs-14` |
| .fs-`*`-`<n>` | Same as the above class besides that this can be used to control the font size depending on screen size. Allowable screen size prefixes are the same as BS4 |
| .fs-decrease | Decreases current font size by 1px |
| .fs-increase | Increases current font size by 1px |
| .text-crop | Same as the `.crop` but for text. All the overflowed characters are replaced by ellipsis |
| .link | Creates underline on links and hides it on hover |
| .link-dotted | Same as `.link` except that underline is dotted |
| .link-dashed | Same as `.link` except that underline is dashed |
| .link-invert | Opposite of `.link`. Creates underlined only on hover |
| .link-invert_dotted | Same as `.link-invert` except that underline is dotted |
| .link-invert_dashed | Same as `.link-invert` except that underline is dashed |
| .nowrap | Prevents text from wrapping at whitespaces. Places all text in one line |
| .lh-`<n>` | Controls the line height in `em`. Allowable values range are from 0 to 10, where 0 is line-height = 1 and 10 is line-height 2  |
