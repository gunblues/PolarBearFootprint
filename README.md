# PolarBearFootprint

Gather User Information 
* Include js: title, desc, url, timestamp, useragent, ip, social network, social id
* Include image: url, timestamp, useragent, ip
  
## Client Side Usage

##### Include js (sniff by fingerprint)
```javascript
  <script async src="your_host/assets/js/pbfp.min.js"></script>
  <script>
      var pbfp = pbfp || {};
      if (typeof(pbfp) === 'object') {
          pbfp.sn = "facebook";  //social network
          pbfp.sid = "123456789";  //social id
      }
  </script>
```

##### Include image (sniff by cookie)
```html
<img src="your_host/pbfp.png">
```  
```remark  
  Why just use our own cookie not php session id? Php session id use cookie too. 
  if user clear the cookie, php session id will be regenerate. In addition, php 
  session needs storage at server side. If your client side have a lot of traffic, 
  that will be the other issue
```

## Installation
  1. Clone the project 
  2. Install php yaf, refer to https://github.com/laruence/yaf
  3. Write your own webserver config by refering to PolarBearFootprint/config
  4. If you want to include js at client side
    * Modify PolarBearFootprint/assets/js/pbfp.js (find "your_host" at file and change it).
    * npm install --save-dev gulp gulp-rename gulp-uglify
    * gulp it!!
  5. cp -r PolarBearFootprint/app/* Your_DocumentRoot
  6. Modify Your_DocumentRoot/application/models/MyAction.php (My Usage is push to redis and then logstash pop from it)
  7. Put code (include js or image) at client side
  8. Start to gather!!
  
## Example
  After Install completely, use browser to open your host and then you will see the image like belowed picture. You can check the page source to see that this page use two methods of including js and image
![alt tag](https://raw.githubusercontent.com/gunblues/PolarBearFootprint/master/example/example.png
)  

## Stress test
#### Test Machine: Google Cloud Platform n1-standard-2
##### Include js will via api so I test it
```report
siege -c10000 -t300S -H 'Content-Type: application/json' 'http://my_host/footprint POST {"fp": "abc","title": "test","desc": "desc","sid": "mysid","sn": "facebook","url": "http://www.google.com","ts": 1487584551, "ua": "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.94 Safari/537.36"}'

Transactions:		      174359 hits
Availability:		      100.00 %
Elapsed time:		      299.82 secs
Data transferred:	        5.32 MB
Response time:		        0.19 secs
Transaction rate:	      581.55 trans/sec
Throughput:		        0.02 MB/sec
Concurrency:		      107.66
Successful transactions:      174359
Failed transactions:	           0
Longest transaction:	        2.70
Shortest transaction:	        0.12
```

##### Include image
```report
siege -c10000 -t300S http://my_host/pbfp.png

Transactions:		      168975 hits
Availability:		      100.00 %
Elapsed time:		      299.71 secs
Data transferred:	       15.31 MB
Response time:		        0.20 secs
Transaction rate:	      563.80 trans/sec
Throughput:		        0.05 MB/sec
Concurrency:		      112.64
Successful transactions:      168975
Failed transactions:	           0
Longest transaction:	        5.21
Shortest transaction:	        0.12
```

##### Network status
```report
PING my_host (x.x.x.x): 56 data bytes
64 bytes from x.x.x.x: icmp_seq=0 ttl=56 time=123.134 ms
64 bytes from x.x.x.x: icmp_seq=1 ttl=56 time=72.499 ms
64 bytes from x.x.x.x: icmp_seq=2 ttl=56 time=69.226 ms
64 bytes from x.x.x.x: icmp_seq=3 ttl=56 time=69.512 ms
64 bytes from x.x.x.x: icmp_seq=4 ttl=56 time=81.412 ms
```

### Machine status
![alt tag](https://github.com/gunblues/PolarBearFootprint/raw/master/test/machine_status_at_stress_test.png
)

## If you want to relay to elasticsearch by logstash
```config
input {
  redis {
      host => "your_redis_host"
      codec => "json"
      data_type => "list"
      key => "footprint"
      # batch_count => 1
      # threads => 1
      type => "footprint"
  }
}
filter {
  if [type] == "footprint" {
    useragent {
        source => "ua"
    }
    geoip {
      source => "ip"
      target => "geoip"
      database => "/etc/logstash/GeoLite2-City.mmdb"
      add_field => [ "[geoip][coordinates]", "%{[geoip][longitude]}" ]
      add_field => [ "[geoip][coordinates]", "%{[geoip][latitude]}"  ]
    }
    mutate {
      convert => [ "[geoip][coordinates]", "float"]
    }
  }
}
output {
    if [type] == "footprint" {
        elasticsearch {
            hosts => ["your_elasticsearch_host:9200"]
            manage_template => false
            index => "polarbearfootprint"
            document_type => "footprint"
            user => "xxx"
            password => "yyy"
            ssl => true
            cacert => "your_path/ca.crt"
        }
        stdout { codec => rubydebug }
    }
}
```
