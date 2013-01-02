# Page Skeleton

Page Skeleton reads a YAML file in your current theme folder called "skeleton.yml" to construct and sync static pages between environments (production, staging, local, etc)

Page Skeleton automatically manages hierarchies (parent pages). There is an example in the included "skeleton.yml" file.

Each page element supports the following attributes:

* title (post_title)
* content (post_content)
* status (post_status) - defaults to "publish"
* template - the filename of the PHP template (directories permitted!!)

All attributes are optional, and on update, *will* override changes that were made directly in WordPress. For example, if you rename a page titled "Hello" to "Goodbye", but skeleton.yml still has "Hello", the page will be renamed back to "Hello".

## Limitations

There is no mechanism to rename page slugs. You'll still have to do this manually.

## Warning

This plugin is made for people with a moderate to high understanding of WordPress structure and theme development. It is meant to help you keep your development / production / staging environments synchronized, while leveraging the power of source control to help manage the structure. That's why the skeleton.yml file is kept inside your theme folder.

We use git.

If you organize your templates in directories, be warned that WordPress 3.3 and earlier will _not_ show them in the "page template" drop-down menu. WordPress 3.4 and later will show one directory level deep. Using Page Skeleton, you can use as many directory levels deep as you like! Just try not to update the page within WordPress (this is not tested behaviour)

## Requirements

* PHP 5.3 or later
* WordPress (tested with 3.3.1 and 3.4.1. YMMV.)
* [Spyc](https://github.com/mustangostang/spyc). Included as a git submodule, so don't forget to git submodule init.

# License

Copyright (C) 2012 Keitaroh Kobayashi, Flagship LLC.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
