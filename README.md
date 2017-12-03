# PHelP

PHelP contains useful tools that PHP doesn't - Helpers for PHP 7.1+

Written by Joseph Cumines - but I would welcome contributions or even just
code reviews.

## Rulebook

1. Helper libraries with no external dependencies (beyond the PHP version)
2. Make logical groupings of functionality, and make an effort to follow best 
    practices
3. Always tested, cover all documented or important cases, at minimum
4. Functional style preferred, immutable objects favoured
5. Minimal or no coupling (between libraries, etc) without sacrificing "DRY"
6. Don't change method signatures without a very good reason, or very low risk
7. Code must be written to be used in production, and must at least have a
    good chance to pass a professional code review
8. As a general rule, all aggregate classes that provide more than one 
    functionality must always use final classes implementing both a singleton
    style `getInstance` method, have no other public static methods, and
    provide a public default constructor for Dependency Injection
9. Unless specifically designed to be stateful, everything must be stateless
10. Clearly document all functionality, it is desirable to be able to know
    what something does at a glance
11. Names like classes and namespaces should be named as if they were all in
    lowercase words, then conjoined, so `JsonObject` not `JSONObject`, for as
    an example.

## Release Plan

This will be maintained, and at this stage I will be regularly adding 
functionality - though it starts from almost nothing. I will be re-writing
some features that I wrote before, some I have wanted before, and probably
just doing what I want.

## Package Overview

This package uses Psr-4 namespaces. The `src` folder is `autoload` and mapped
to `JoeyCumines/Phelp`. The `tests` folder is `autoload-dev` and mapped to
`Tests/JoeyCumines/Phelp`.

The following headings are based on the namespaces nested within those two.

### Algorithms

Solutions, grouped by functionality rather than concepts or category for
better code structure. The code in here **WILL CHANGE** at a much greater rate
than code in the `JoeyCumines/Phelp/Helpers` namespace.

### Utilities

Anything that leans towards solving a specific problem instead of providing
a more general solution. A good example of this would be testing utilities,
which fill a very specific niche.

### Helpers

"Helper" classes implement and document a thin layer between implementations
in `JoeyCumines/Phelp/Algorithms`. Somewhat like the separation between the
controller and service layer in many modern frameworks.

### Interfaces

All interfaces, provided to be used as dependencies (in place of a concrete
class), are here.

### Adaptors

Concrete implementations of interfaces, that's it.

## Contributing

Do what you want, I love talking about the best way to solve problems. If you
think that a certain way is better, or even just want to have a crack at
adding something, go for your life, I am happy to review PR's even if it's
just something relevant you wrote that you want feedback for. I am not at
all possessive about this project, and I am happy to incorporate any new code
under the same license, if it's something I can agree on.
