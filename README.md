# Selio

Selio is the result of my 3-year long experience in the domain of web development. It is the codebase that I've developed over the years and use as a jump-start backend when creating new websites. I've atempted to make it as lightweight, configureable, extensible and simple as possble. Selio is tested and runs on [PHP](http://php.net/) 7.0 and above.

Selio allows for object oriented component based page building and ajax handling which is easily extensible and scalable. Refer to the [documentation](https://github.com/muqg/Selio/wiki) for more information (or by the lack of such, you can temporarily visit the [quickstart](https://github.com/muqg/Selio/blob/master/Quickstart.md)).

## Features
Selio's .htaccess is configured to route URLs to root (/) path and then parses the URL within a PHP controller which determines and loads a relevant page or ajax handler. Amongst Selio's features are:

- Object oriented page generation and AJAX handling
- Reusable and extensible page components
- URL routing
- Localization
- Caching
- Authentication
- Administration panel
- Automatic database connections
- Lots of common utilities

## Installation
Download the source and move all contents from the **dist** folder to your desired folder. Then execute the **setup.php** script which performs any installation steps. Then navigate to **source/include** path and there you will find:

- **settings.php** where you can manually manage settings.
- **db.php** containing database connection information in format initially presented in the file.
- **.htpasswd** containing login information for the administration. The login information is in the same format as traditional .htpasswd files and is a temporary method for administration authentication. The difference is that this file's password is encoded using [password_hash](http://php.net/manual/en/function.password-hash.php) function instead of md5. Feel free to manually change the password and username.

Then check out the [quickstart](https://github.com/muqg/Selio/blob/master/Quickstart.md) guide for more information.

**Selio currently supports PHP 7.0+**

## Updating
In order to update an existing Selio application download the source and place the **dist** folder inside your website root folder. Then place the **update.php** inside the **dist** folder and run the **dist/update.php** script. That's all...

## About me...
I am a dentistry student and a programming enthusiast, primarily coding for university projects, friends' websites and myself. I love automating things making my everyday life and that of others easier with the technologies available all around (ehem, [Python](https://www.python.org/)... ehem).

About my coding adventures... I have never used [PHP](http://php.net/) frameworks such as [Wordpress](https://wordpress.org/), [Laravel](https://laravel.com/) and others, but I have used a lot of different technologies over the years. Initially the idea was to learn to code in order ot create cool games with [Unity](https://unity3d.com/) and it is where the journey started... Over the years I've used things such as [WinForms](https://en.wikipedia.org/wiki/Windows_Forms), [WPF](https://en.wikipedia.org/wiki/Windows_Presentation_Foundation), [CEF](https://en.wikipedia.org/wiki/Chromium_Embedded_Framework), [PyQt](https://riverbankcomputing.com/software/pyqt/intro), [jQuery](https://jquery.com/) and more recently [Electron](https://electronjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/) and the list goes on and on since I am from the very curious and explorative type of people.

So what is up with PHP and all the website thingies? Well, not long after me and a friend quit making games because we were unable to find an aspiring deisgner I found out that a friend of mine was trying himself to create a website for his company. Ever since then I have been developing it for him as a hobby. It is called [Lustro](https://lustro.bg/en) and while it is not perfect it is more than I've ever thought that I could achieve all by myself. (Note that I am not the one maintaining it, just the one making it happen) Yes... It is running [Selio](https://github.com/muqg/Selio), please do not break it just to show me that I am a miserable developer... thanks...

Now that I am nearing the end of my dentistry education, I am quite busy with my studies and I will have to leave web development behind a bit, since... well... priorities... While I do NOT think that I am bringing anything new, nor interesting I would be happy if you share your opinion or even help me make this little project better. It is the first time I present my code before the open eyes of the world.

I am 22 years old and I am from Bulgaria, I started programming for the first time in 2014 and I have learned it all by myself... __and to all of you who've made it this far, remember... that you can be anything you want, but not everything you want.__
