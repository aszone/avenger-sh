# PHP Avenger

> PHP Avenger is a future collection open source of tools writting in PHP with focus in security and hacking.

### Beta

* PHP Avenger sh ( Search Enginer )

### Future Implementation

* PHP Avenger bt ( Brute - Force )
* PHP Avenger sca ( State Code Analayse )
* PHP Avenger pwp ( Plugin WordPress )
* PHP Avenger cj ( Component Joomla )

***
#PHP Avenger SH

> Php Avenger sh is a open source tool with ideia baseaded in fork old inurlbr by Cleiton Pinheiro. Basicaly **PHP Avenger sh** is a tool automates the process of detecting of possibles vunerabilities in using mass scan and check if true or false. Php Avenget utility search enginers with google, bing and others using dorks ( avanced searching ).

## Instalation Php Avenger SH

The recommended way to install PHP Avenger is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest beta version of Php Avenger SH:

```bash
php composer.phar require aszone/avenger-sh
```
## Basic Usage

> Use command for init process, result will print in monitor and save in txt on folder results. 

### Check Sql Injection
```bash
php avenger sh --dork="site:com.cl inurl:php?id=" --check="sqli"
```

### Check Local File Download
```bash
php avenger sh --dork="site:com.cl inurl:download.php?file=" --check="lfd"
```

### Check is Admin Page
```bash
php avenger sh --dork="site:com.cl inurl:admin" --check="isAdmin"
```
## Details

#### Searchs Enginers
* Google
* GoogleApi
* Bing
* DukeDukeGo
* Yahoo
* Yandex

#### Vulnerabilities Checked
* Sql Injection
* Local File Download
* Admin Page

#### Future Vulnerabilities Checked
* RFI
* Xss
* Sensitive Files
    * Dump Files
    * Config Files
    * Open Folders

#### Features development
* Power Search
* Send Mail for results
* Name of TXT
* Proxys
    * TOR
    * Site of Proxys
    * **Virgem Proxys**
    
## Help and docs
* [Documentation](http://phpavenger.aszone.com.br).
* [Examples](http://phpavenger.aszone.com.br/examples).
* [Videos](http://youtube.com/aszone).
* [Steakoverflow](http://phpavenger.aszone.com.br).

