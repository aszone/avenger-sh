# PHP Avenger

> PHP Avenger is a modern collection of open source tools written in PHP with focus in security and hacking.

### Beta

* PHP Avenger sh ( Search Engine )

### Future Implementation


* PHP Avenger bt ( Brute - Force )
* PHP Avenger sca ( State Code Analayse )
* PHP Avenger pwp ( Plugin WordPress )
* PHP Avenger cj ( Component Joomla )

***
#PHP Avenger SH

> Php Avenger sh is a open source tool with an idea **based in a fork inurlbr by Cleiton Pinheiro**. Basically **PHP Avenger sh** is a tool that automates the process of detecting possible vulnerabilities using mass scan and checking if the vulnerability is true or false. Php Avenger uses search engines like google, bing and others through dorks ( advanced search ).

## Installation 

The recommended way to install PHP Avenger is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest beta version of Php Avenger SH:

```bash
php composer.phar create-project aszone/avenger-sh
cd avenger-sh
```
## Basic Usage

> Use the commands bellow to init the process, results will be printed in the monitor and saved in a `.txt` file on folder `results`.

### Get trash search

```bash
php avenger sh --dork="site:com.ar ext:sql password"
```
#### Result of trash search
![alt tag](http://lenonleite.com.br/wp-content/uploads/2016/06/imagem1.png)


### Check Sql Injection
```bash
php avenger sh --dork="site:com.ar inurl:php?id=" --check="sqli"
```
#### Result of Sql Injection
![alt tag](http://lenonleite.com.br/wp-content/uploads/2016/06/imagem2.png)
![alt tag](http://lenonleite.com.br/wp-content/uploads/2016/06/imagem3.png)


### Check Local File Download
```bash
php avenger sh --dork="site:com.ar inurl:download.php?file=" --check="lfd"
```
#### Result of Local File Download
![alt tag](http://lenonleite.com.br/wp-content/uploads/2016/06/imagem7.png)


### Check is Admin Page
```bash
php avenger sh --dork="site:com.ar inurl:admin" --check="isAdmin"
```

### Help for commands
```bash
php avenger sh 
```

## Details

#### Search Engines
* Google
* GoogleApi
* Bing
* DukeDukeGo
* Yahoo
* Yandex

#### Covered Vulnerabilities
* Sql Injection
* Local File Download
* Admin Page

#### Covered Vulnerabilities in next versions
* RFI
* Xss
* Sensitive Files
    * Dump Files
    * Config Files
    * Open Folders

#### Features under development
* Power Search
* Send E-mail with results
* Naming the `.txt` result file
* Proxys
    * TOR
    * Site of Proxys
    * **Virgin Proxys**
    
## Help and docs
* [Documentation](http://phpavenger.aszone.com.br).
* [Examples](http://phpavenger.aszone.com.br/examples).
* [Videos](http://youtube.com/aszone).
* [Steakoverflow](http://phpavenger.aszone.com.br).

