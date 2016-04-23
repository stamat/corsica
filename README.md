# CORSica
![corsica old flag](logo.png)

A very simple HTTP CORS proxy written in PHP using cURL

## How it works?
You provide $domain of the target server. Every HTTP header is copied except `Redirect`, `Origin` and `Host` where Origin and Host become the value of $domain. Then proxy just sends the edited request to the target server using cURL. When the response is returned the response from proxy to client is extended with the target server's headers. That's about it.
You just host this `corsica.php` file in your local setup or on your server, and you are all set. Mind the .htaccess that forwards all of the requests to the requests to the `corsica.php`proxy, if you need it...

## The proxy is not finished yet!!!
Currently the proxy is intended for RESTful API endpoints, this has to be changed for the proxy to be adaptable to any situation. Also detecting the file name of the script shouldn't be hardcoded to public_html.
The code is very simple, you'll figure it out before I even make the final improvements and adapt it to your needs.




Meilleures salutations,

Stamat

April 2016. Montpellier, France
