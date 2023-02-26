# floorfy downloader virtual tour web viewer

## What does

floorfy-dl downloads cubemap skybox 360 and equirectangular panos photos of floorfy houses virtual tours and selfconfigure vtour.json pannellum virtual tour web viewer to watch the floorfy virtual tour downloaded.  
Floorfy official viewer seems to be based in Pannellum code, same json variables names, so it's a easy thing.  

This project is a merge of https://github.com/fdd4s/floorfy-dl and https://github.com/mpetroff/pannellum with light modifications over both projects.

## Dependencies

php, php-curl, curl  

This code can run over Linux and Windows  

## Usage

    $ php ./floorfy-dl.php <floorfy url>  

e.g: If the url is https://floorfy.com/tour/417174 you have to run the script this way:  

    $ php ./floorfy-dl.php https://floorfy.com/tour/417174  

It will download all the skybox and equirectangular images.  
And it will configure vtour.json to play the virtual tour with vtour.html  
Host in a webserver all skybox jpg, vtour.json, html, css, svg, png and js files to play the virtual tour, in the same folder.  
Equi jpg files are not needed to play the virtual tour in the web viewer, it can be viewed with Ricoh Theta app and Panini.  
The webserver only needs basic static configuration (no php needed to play the virtual tour).  

A live demo of this script can be seen in http://openpano.rf.gd/vtour.html

## Credits

Created by fdd  
Send feedback and questions to fdd4776s@gmail.com  
Support future improvements of this software https://www.buymeacoffee.com/fdd4s  
floorfy-dl.php and arrow.png are public domain https://unlicense.org/  
Rest of files are from Pannellum and it has MIT license https://github.com/mpetroff/pannellum  
