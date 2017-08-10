# HologramPlusAPI

**What does this project do?**

This lightweight project enhances the [`Hologram.io`](https://hologram.io) Rest [API for sending messages](https://hologram.io/docs/reference/cloud/http/#/reference/hologram-cloud/sms/send-sms-to-a-device) by adding useful features & variables. These include:

- Automatically send long SMS as multiple 160 character messages -  see [overflow](#overflow)
- SMS variables (handled server side) - see [variables](#variables)
- Easy SMS formatting - see [formatting](#formatting) 

NOTE: You can use the demo server over at [`http(s)://s.samdd.me/hologram`](https://s.samdd.me/hologram) if you don't want to host your own instance.

Here are some examples of what you can do:
### Basic hello world
    hologram.php?key=KEY&id=device
> [12:30] Hello world! (No message specified)

### Variables
    hologram.php?key=KEY&id=device&body=IP={$IP$}
> IP=34.168.56.2

<br>

    hologram.php?key=KEY&id=device&body=The time is {$time$}
> The time is 12:30

<br>

    hologram.php?key=KEY&id=device&body=Hello{$nl$}World
> Hello
> World

<br>

    hologram.php?key=KEY&id=device&body={$pre$}Example title{$nl2$}Lorem ipsum
> [12:30]: Example title
> 
> Lorem ipsum

### Formatting

    hologram.php?key=KEY&id=device&title=Test&body=Hello world
> [12:30]: Test: Hello world

<br>

    hologram.php?key=KEY&id=device&title=Notification{$nl$}&body=Lorem ipsum

> \[12:30\]: Notification:
> 
> Lorem ipsum

## SMS configuration
### Overflow
By default messages are split into multiple SMS, though this can easily be change by using the `?overflow` parameter. 

    overflow=multiple     (default) Messages will be split into multiple SMSs
    overflow=trim         Messages will be trimmed so that they are 160 characters long
    overflow=fail         Messages will not be sent if they exceed 160 characters

### Timezone
By default `HologramPlusAPI` uses the `Europe/London` timezone, but this can easily be changed by specifying the `?timezone` parameter. Refer to the [timezone lists here](http://php.net/manual/en/timezones.php)

    timezone=America/New_York     Change the {$time$} variables to the New York timezones.

### Footer
Normally only one message is sent (aka the title+body). You can specify a message to be sent after all the other messages are sent by using the `?footer` parameter. Example:

    footer=//END OF MESSAGE      Send a separate SMS after the main one(s) 

### Sandbox
If you want to test out your messages without actually sending them, add the `&sandbox` parameter to the URL.

## Example URLs
    https://s.samdd.me/hologram?key=KEY&id=DEVICE
    https://s.samdd.me/hologram?key=KEY&id=DEVICE&title=Hello{$nl$}&body=World
	https://s.samdd.me/hologram?key=KEY&id=DEVICE&overflow=trim&body=REALLY LONG MESSAGE...
	https://s.samdd.me/hologram?key=KEY&id=DEVICE&timezone=America/New_York&body={$time2$}
	https://s.samdd.me/hologram?key=KEY&id=DEVICE&body=Sender IP={$ip$}

## Full Usage documentation:
	USAGE:

	REQUIRED:
		?key  = Hologram API Key
		?id   = Hologram device ID 

	OPTIONAL:
		?body 		= The message to be sent to the device (Messages will be split up if over 160 characters, unless overflow parameter set)
						Default:	[TIME] Message delivered

		?from 		= The number to send the message from
						Default = 7367125
								  TESTING

		?timezone	= The PHP timezone to use
						Default: 	Europe/London

		?overflow	= How to handle messages exceeding 160 characters
							multiple	| split messages into multiple SMSs
							trim		| trim end of messages
							fail		| don't send message if it exceeds 160 characters
						Default: multiple

	    ?title		= Prefix message with a title, format=[H:M] TITLE:
	    				EXAMPLE: '[12:30] Example title: '
	    				Default: none

	    ?footer		= Send a separate SMS after the content SMS's (eg. for a link / message separator SMS etc.)

	    ?sandbox	= Prevents the message from being sent, (use for API testing purposes)

	BODY VARIABLES (case sensitive):
		{$time$}	The current time, format=H:M
					EXAMPLE: '12:30'

		{$time2$}	The current time, format=H:M:S
						EXAMPLE: '12:30:05'

		{$pre$}		Message time prefix, format=[H:M:S]:
						EXAMPLE: '[12:30]: '

		{$ip$}		IP Address 

		{$nl$} 		Insert newline

		{$nl2$} 	Insert two newlines

		{$nl3$} 	Insert three newlines