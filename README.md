# PolarBearFootprint

Gather User Information 
* Include js: title, desc, url, timestamp, useragent, ip
* Include image: url, timestamp, useragent, ip
  
## Client Side Usage

##### Include js (sniff by fingerprint)
```javascript
  <script async src="your_host/assets/js/pbfp.min.js"></script>
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
    * npm install
    * gulp -h your_host
  5. Modify PolarBearFootprint/app/application/models/MyAction.php (My Usage is push to redis and then logstash pop from it)
  6. If you want to use PolarBearFootprint/app/application/library/MyRedis.php, please modify redis hot at PolarBearFootprint/app/conf/application.ini    
  7. cp -r PolarBearFootprint/app/* Your_DocumentRoot
  8. Put code (include js or image) at client side
  9. Start to gather!!
  
## Example
  After Install completely, use browser to open your host and then you will see the image like belowed picture. You can check the page source to see that this page use two methods of including js and image
![alt tag](https://raw.githubusercontent.com/gunblues/PolarBearFootprint/master/example/example.png
)  

## If you want to relay to elasticsearch by logstash
#### 01-polar-bear-footprint.conf
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
      source => "clientip"
      target => "geoip"
      database => "/etc/logstash/GeoLite2-City.mmdb"
    }
    mutate {
      remove_field => ["ua", "clientip", "@timestamp"]
    }
  }
}
output {
    if [type] == "footprint" {
        elasticsearch {
            hosts => ["your_elasticsearch_host:9200"]
            manage_template => false
            action => update
            upsert => '{
                "fp" : "%{fp}",	 
                "cookie" : "%{cookie}",			
                "url" : "%{url}",
                "scheme" : "%{scheme}",
                "hostname": "%{hostname}",
                "path": "%{path}",
                "query": "%{query}",
                "fragment": "%{fragment}",
                "os": "%{os}",
                "minor": "%{minor}",
                "os_minor": "%{os_minor}",
                "os_major": "%{os_major}",
                "patch": "%{patch}",
                "major": "%{major}",
                "@version": "%{@version}",
                "name": "%{name}",
                "os_name": "%{os_name}",
                "device": "%{device}",
                "created": "%{created}",
                "geoip": {
                    "timezone": "%{[geoip][timezone]}",
                    "ip": "%{[geoip][ip]}",
                    "latitude": %{[geoip][latitude]},
                    "longitude": %{[geoip][longitude]},
                    "city_name": "%{[geoip][city_name]}",
                    "continent_code": "%{[geoip][continent_code]}",
                    "country_code2": "%{[geoip][country_code2]}",
                    "country_code3": "%{[geoip][country_code3]}",
                    "country_name": "%{[geoip][country_name]}",
                    "location": {
                        "lon": %{[geoip][longitude]},
                        "lat": %{[geoip][latitude]}
                    }
                }
            }'
            index => "pbtest"
            document_type => "footprint"
            document_id => "%{id}"
            user => "xxx"
            password => "yyy"
            ssl => true
            cacert => "your_path/ca.crt"
        }
        stdout { codec => rubydebug }
    }
}                
```
#### 01-polar-bear-fingerprint.conf
```
input {
  redis {
      host => "your_redis_host"
      codec => "json"
      data_type => "list"
      key => "fingerprint"
      # batch_count => 1
      # threads => 1
      type => "fingerprint"
  }
}
filter {
  if [type] == "fingerprint" {
    useragent {
        source => "ua"
    }
    mutate {
      remove_field => ["ua", "@timestamp"]
    }
  }
}
output {
    if [type] == "fingerprint" {
        elasticsearch {
            hosts => ["your_elasticsearch_host:9200"]
            manage_template => false
            action => update
            upsert => '{
                "updated" : "%{updated}",
                "os": "%{os}",
                "minor": "%{minor}",
                "os_minor": "%{os_minor}",
                "os_major": "%{os_major}",
                "patch": "%{patch}",
                "major": "%{major}",
                "@version": "%{@version}",
                "name": "%{name}",
                "os_name": "%{os_name}",
                "device": "%{device}"
            }'
            index => "pbtest"
            document_type => "fingerprint"
            document_id => "%{id}"
            user => "xxx"
            password => "yyy"
            ssl => true
            cacert => "your_path/ca.crt"
        }

        stdout { codec => rubydebug }
    }
}
```
#### 01-polar-bear-fingerprint.conf
```
input {
  redis {
      host => "10.140.0.8"
      codec => "json"
      data_type => "list"
      key => "cookie"
      # batch_count => 1
      # threads => 1
      type => "cookie"
  }
}
filter {
  if [type] == "cookie" {
    useragent {
        source => "ua"
    }
    mutate {
      remove_field => ["ua", "@timestamp"]
    }
  }
}
output {
    if [type] == "cookie" {
        elasticsearch {
            hosts => ["10.140.0.7:9200"]
            manage_template => false
            action => update
            upsert => '{
                "updated" : "%{updated}",
                "os": "%{os}",
                "minor": "%{minor}",
                "os_minor": "%{os_minor}",
                "os_major": "%{os_major}",
                "patch": "%{patch}",
                "major": "%{major}",
                "@version": "%{@version}",
                "name": "%{name}",
                "os_name": "%{os_name}",
                "device": "%{device}"
            }'
            index => "pbtest"
            document_type => "cookie"
            document_id => "%{id}"
            user => "elastic"
            password => "breaktime168"
            ssl => true
            cacert => "/home/kevin/es/config/ca/ca.crt"
        }

        stdout { codec => rubydebug }
    }
}
```
#### 02-webpage-urltask.conf
```config
input {
  redis {
      host => "your_redis_host"
      codec => "json"
      data_type => "list"
      key => "urltask"
      # batch_count => 1
      # threads => 1
      type => "urltask"
  }
}
filter {
  if [type] == "urltask" {
    mutate {
      remove_field => ["ua", "@timestamp"]
    }
  }
}
output {
    if [type] == "urltask" {
        elasticsearch {
            hosts => ["your_elasticsearch_host:9200"]
            manage_template => false
            action => "update"
            upsert => '{
                "url" : "%{url}",
                "scheme" : "%{scheme}",
                "hostname": "%{hostname}",
                "path": "%{path}",
                "query": "%{query}",
                "fragment": "%{fragment}",
                "task_updated": "%{task_updated}",
                "status": "init"
            }'
            index => "uttest"
            document_type => "urltask"
            document_id => "%{id}"
            user => "xxx"
            password => "yyy"
            ssl => true
            cacert => "your_path/ca.crt"
        }
        stdout { codec => rubydebug }
    }
}
```
#### elasticsearch mapping
```mapping
PUT polarbearfootprint
{
  "mappings": {
    "footprint": {
      "_all": {
        "enabled": false
      },
      "properties": {
        "fp": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "cookie": {
          "type": "keyword",
          "index": "not_analyzed"
        },	
        "url": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "scheme": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "hostname": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "port": {
          "type": "long"
        },
        "path": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "query": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "fragment": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "major": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "minor": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os_name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "params": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "patch": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "device": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "stay": {
          "type": "long"
        },
        "title": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "created": {
          "type": "date"
        },
        "geoip": {
          "properties": { 
            "timezone": {
              "type": "keyword"
            },
            "ip": {
              "type": "ip"
            },
            "latitude": {
              "type": "float"
            },
            "longitude": {
              "type": "float"
            },
            "city_name": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "continent_code": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "country_code2": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "country_code3": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "country_name": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "region_code": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "region_name": {
              "type": "keyword",
              "index": "not_analyzed"
            },
            "location": {
              "type": "geo_point"
            },
            "postal_code": {
              "type": "keyword",
              "index": "not_analyzed"
            }
          }
        }
      }
    },
    "fingerprint": {
      "_all": {
        "enabled": false
      },
      "properties": {
        "sex": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "age": {
          "type": "long"
        },
        "major": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "minor": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os_name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "params": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "patch": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "device": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "updated": {
          "type": "date"
        },
        "calculate_keywords_date": {
          "type": "date"
        }	
      }
    },
    "cookie": {
      "_all": {
        "enabled": false
      },
      "properties": {
        "major": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "minor": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "os_name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "name": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "params": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "patch": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "device": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "updated": {
          "type": "date"
        },
        "calculate_keywords_date": {
          "type": "date"
        }	
      }
    }    
  }
}

PUT webpage
{
  "mappings": {
    "urltask": { 
      "_all": { "enabled": false  }, 
      "properties": { 
        "url": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "scheme": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "hostname": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "port": {
          "type": "long"
        },
        "path": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "params": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "query": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "fragment": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "status": {
          "type": "keyword",
          "index": "not_analyzed"
        },
        "task_updated": {
          "type": "date"
        },
        "fetch_date": {
          "type": "date"
        },
        "page_id": {
          "type": "keyword",
          "index": "not_analyzed"
        }
	  }
    },
    "page": { 
      "_all": { "enabled": false  }, 
      "properties": { 
        "title": {
          "type": "text"
        },
        "content": {
          "type": "text"
        },
        "updated": {
          "type": "date"
        },
        "urllist": {
          "type": "keyword",
          "index": "not_analyzed"
        }	
      }
    }
  }
}
```
