# Fups #

* What's fups ant what it's not?
* Installation
* Usage
* Actions list

## What's Fups ant what it's not? ##

Fups (FTP upload service) is a PHP script for FTP uploads and recreations project's local structure on the server. It's not a standard FTP CLI client application. You cannot modify or delete items directly on the server. The goal is to make regular files updates easy and fast.

## Installation ##

Clone git repository:

```
$ git clone https://github.com/piotrkabacinski/fups
```

After you clone the files I strongly recommend to create **bash alias** for faster script invoking. In this documentation I'm going to use **fups** alias as an example:

```
alias fups="php /path/to/fups/fups"
```

To test the installation simple lunch "hello" fups action.

```
$ fups hello
```
## Usage ##

Usage of Fups is quite similar to other appliactions - just select file or directory and let the script upload it to the server. The main difference is the uploads method. For example: uploading assets/pdf/document.pdf into empty location on the server the document.pdf won't appear in the "root" directory. Fups will check if the same path exist on the server, if not it will be created. Uploaded file will appear in /assets/pdf/document.pdf on the server. Just like it's local.

To initialize application type "cf" action within your project's home folder:

```
$ fups cf
```

"cf" stands for "create fups". It makes an unique json file for project's directory that will contain access data for the future connections to the server. To get connection file name and it's path for configure type "cfname" action:

```
$ fups cfname
/path/to/fups/classes/../fups_connects/aaa111bbb.json
$ nano /path/to/fups/classes/../fups_connects/aaa111bbb.json
```
```
{
  "connection" : {
    
    "host" : "",
    "login" : "",
    "password" : "",
    "port" : 21
    
  },
  
  "dir" : "/"
}
```
The "dir" value represents path to the parent location on the server where the uploads will be done. After you fill up and save the file you are ready to go! Use "test" action to make a connection to the server in selected location:

```
$ fups test
Connected succesfully to ftp.example.com @ /project
```

To start uploading use "u" action. As a parameter you can choose:

* File:
```
$ fups u directory/index.html
```
* Direcotry and it's content:
```
$ fups u css/
```
* Group of these objects seperated by ";":
```
$ fups u "css/;assets/pdf/document.pdf;index.html"
```
## Actions ##

Action:  | Result 
:---------|:-----
help      |List of actions
hello     |Inits script's hello message
u         |uploads file
cf        |creates json connection file for the local directory
rmcf      |removes json connection file
test      |makes a test connection to the server and selected directory
