# PolarBearFootprint

Gather User Information 
* Include js: title, desc, url, timestamp, useragent, ip, social network, social id
* Include image: url, timestamp, useragent, ip
  
## Client Side Usage

##### include js (sniff by fingerprint)
```javascript
  <script async src="your_host/assets/js/pbfp.js"></script>
  <script>
      var pbfp = pbfp || {};
      if (typeof(pbfp) === 'object') {
          pbfp.sn = "facebook";  //social network
          pbfp.sid = "123456789";  //social id
      }
  </script>
```

##### include image (sniff by cookie)
```html
<img src="your_host/pbfp.png">
```

## Installation
  1. Clone the project 
  2. Install php yaf, refer to https://github.com/laruence/yaf
  3. Write your own webserver config by refering to PolarBearFootprint/config
  4. cp -r PolarBearFootprint/app/* Your_DocumentRoot
  5. Modify Your_DocumentRoot/application/models/MyAction.php (My Usage is push to redis and then logstash pop from it)
  6. Put code (include js or image) at client side
  7. Start to gather!!
  
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
