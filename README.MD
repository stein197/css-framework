# Template

Basic CSS and JS development kit based on Bootstrap 4. Most of the classes can be applied to any BS4-compatible breakpoint (like classes `.fs-20` and `.fs-md-20`) as well as BS4's classes.

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

## Table of contents

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
