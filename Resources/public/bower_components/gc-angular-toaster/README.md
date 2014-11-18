Good Coders - Angular Toaster
gc-angular-toaster
=================

**AngularJS Toaster** is a AngularJS port of the **toastr** non-blocking notification jQuery library. It requires AngularJS v1.2.6 or higher and angular-animate for the CSS3 transformations. 
(I would suggest to use /1.2.8/angular-animate.js, there is a weird blinking in newer versions.)

### Current Version 1.0.0

#### Breaking Changes from previous version

* bootstrap 3 and beyond... (no limit on toaster types)
* toaster is used by passing an object instead of params
* "toaster.pop" has been renamed to "toaster.add" to avoid confusion with javascript Array functions pop and push
FROM
```javascript
    toaster.pop('success','We love objects!','A non blocking message!',null,);
```
TO
```javascript
    toaster.add({
        type: 'success',
        heading: 'We love objects!',
        message: 'A non blocking message!',
        html
    });
```
* The type of toasters is no longer limited. Instead you can add a toaster of any type. This simply add that class to your toaster message div and allows you to style it.
```javasript
    toaster.add({
        type: 'danger',
        heading: 'An error Occurred',
        message: 'I used to default to info, but now I can be my own message!',
        html
    });
```
Simply add css in order to accommodate additional toaster types
```
.toast-danger {
  background-color: #bd362f;
}
```


## Demo
- Simple demo is at http://plnkr.co/edit/02sGem4axqlPAKGKBwbS?p=preview (with version 1.0.0)

## Getting started

1. Link scripts:

```html
<link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet" />
<link href="http://cdnjs.cloudflare.com/ajax/libs/angularjs-toaster/0.4.4/toaster.css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.0/angular.min.js" ></script>
<script src="http://code.angularjs.org/1.2.0/angular-animate.min.js" ></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/angularjs-toaster/0.4.4/toaster.js"></script>
```

2. Add toaster container directive: `<toaster-container></toaster-container>`

3. Prepare the call of toaster method:

```js
	// Display an info toast with no title
	angular.module('main', ['gc.toaster'])
	.controller('myController', function($scope, toaster) {
	    $scope.showMessage = function(){
	        toaster.add({
	        type:'success',
	        title: "title",
	        message: "text"
	        });
	    };
	});
```

4. Call controller method on button click:

```html
<div ng-controller="myController">
    <button ng-click="showMessage()">Show a Toaster</button>
</div>
```

### Other Options

```html
// Change display position
<toaster-container toaster-options="{'position-class': 'toast-top-full-width'}"></toaster-container>
```

### Animations
Unlike toastr, this library relies on ngAnimate and CSS3 transformations for animations.
		
## Author
**Jiri Kavulak**

## Credits
Inspired by http://codeseven.github.io/toastr/demo.html.

## Copyright
Copyright © 2013 [Jiri Kavulak](https://twitter.com/jirikavi).

## License 
AngularJS-Toaster is under MIT license - http://www.opensource.org/licenses/mit-license.php

