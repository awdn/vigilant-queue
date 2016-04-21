# vigilant-queue
React-PHP based priority queue key-value storage for expiration time based object eviction.

## Objective
The objective is to create a prioritized list ordered by an expiration time of the item. When querying the list if there is an expired item by a given threshold, the list should return the item with the lowest expiration time below the threshold and then remove the item from the list. Each item has a unique key (which is allowed to be only once within the list), as well as a data attribute and an expiration time. If an item should be added to the list and the key already exists, the values for the expiration time and data of the existing item have to be update within the list. The list has to be reordered based on the new priority of the item as well.

## Use Case
Imagine a distributed web environment where actions in your application have to be taken based on frequently occurring events for the same entities. If these actions are very cost intensive it probably makes sense to execute them only once by buffering the events for a while and fetch the latest scheduled action from the queue as soon as the expiration time has been reached.

### First Approach
This approach is based on a doubly linked list. Inserts have to be done ordered based on the expiration time, so that the item with the lowest expiration time is at the beginning of the list. From there it can be fetched with a _pop()_ operation. When updating an existing item it has to be moved within the list, if the expiration time has changed.
_Advantage_: Very intuitive. No redundant data. Everything can be implement within a list. 
_Disadvantage_: Needs to traverse the list very often when inserting or updating item. Cost intensive in terms of CPU time.

### Second Approach
A PriorityQueue (implemented as _min-heap_), where each item is not necessarily unique, supported by two HashMaps holding the data and the most recent priority for a given key.
When querying the queue for an item to be evicted the top element of the queue will be checked, if its expiration time is equal or lower than a given threshold (current time). To validate if this is the most recent version of the item, the expiration time of the top item from the queue will be compared with the latest known priority for the given key from the HashMap. If this is the the case, the item will be returned from the _evict()_ function, otherwise it will return _null_. In both cases the item will be removed from the queue and from both HashMaps.
_Advantage_: No need for cost intensive traversals of a list.
_Disadvantage_: Redundant data within the PriorityQueue.

## Pseudo code visualising the second approach
```
class PriorityHashQueue
    PriorityQueue <Key, Prio> q
    ArrayObject<Key, Data> d
    ArrayObject<Key, Prio> p
    
    push(Key, Data, Prio):
            d.set(Key, Data)
            p.set(Key, Prio)
            q.insert(Key, Prio)
    
    
    evict(Threshold):
        if (q.valid())
            item = q.top()
            if (item.prio < Threshold)
            q.next()
            if (p.get(item.key) == item.prio)
                data = d.get(item.key)
                d.unset(item.key)
                p.unset(item.key)
                return data
        return null
```

## Run the example
Change directory into the _examples_ sub folder, open three terminals and run the following commands.

### Starting the process
#### Starting the queue server
```
$ php deferred-queue-daemon.php --debug 1 --evictionTickrate 1000
```

#### Starting a consumer process which just collects the evicted messages
```
$ php console-consumer.php --debug 1
```

#### Starting a producer simulation which generates some data:
```
$ php console-producer.php --debug 1 --simulate 1 --keyPrefix mk --keyDistribution 1 --numMessages 1 --expMinMs 3000000 --expMaxMs 3000000 --sleepUs 0
```

### Output
#### Producer
Producing a new message which is then send to the queue:
```
2016-04-20 21:33:23 - Using tcp://127.0.0.1:4444 for inter process communication.
2016-04-20 21:33:24 - Generating packets...
2016-04-20 21:33:24 - mk_1:3000000:string|8a0b582099de9d149939c871449ebca96bac73ee
```
#### Queue Server
See here how a message comes in which is evicted three seconds later:
```
2016-04-20 21:33:22 - Running Awdn\VigilantQueue\DeferredQueueServer
2016-04-20 21:33:22 - The eviction tick rate is set to 1000/second.
2016-04-20 21:33:22 - Binding inbound ZMQ to 'tcp://127.0.0.1:4444'.
2016-04-20 21:33:22 - Binding outbound ZMQ to 'tcp://127.0.0.1:5444'.
2016-04-20 21:33:23 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:23 - [STATS] Added objects: 0, evictions: 0 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:33:24 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:24 - [STATS] Added objects: 0, evictions: 0 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:33:24 - [OnMessage] Data for key 'mk_1' [type 'string', exp 3000000 ms]: '8a0b582099de9d149939c871449ebca96bac73ee'
2016-04-20 21:33:25 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:25 - [STATS] Added objects: 1, evictions: 0 (1 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:33:26 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:26 - [STATS] Added objects: 1, evictions: 0 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:33:27 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:27 - [STATS] Added objects: 1, evictions: 0 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:33:27 - [Eviction] Timeout detected for 'mk_1' at 1461231207.852
2016-04-20 21:33:28 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:33:28 - [STATS] Added objects: 1, evictions: 1 (0 Obj/Sec, 1 Evi/Sec).
```
#### Consumer
Consume the evicted message:
```
2016-04-20 21:33:24 - Connect to zmq at 'tcp://127.0.0.1:5444' (incoming evicted jobs from queue).
2016-04-20 21:33:27 - Received message: 8a0b582099de9d149939c871449ebca96bac73ee
```

## Benchmark
The [benchmark](https://github.com/awdn/vigilant-queue/blob/master/Benchmark.md) shows how the server receives 1.000.000 messages, puts them on the queue and evicts based on the defined expiration timeout.
