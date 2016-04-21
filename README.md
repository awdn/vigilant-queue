# vigilant-queue
React-PHP based priority queue key-value storage for expiration time based object eviction.

## Objective:
The objective is to create a prioritized list ordered by an expiration time of the item. When querying the list if there is an expired item by a given threshold, the list should return the item with the lowest expiration time below the threshold and then remove the item from the list. Each item has a unique key (which is allowed to be only once within the list), a data attribute as well as an expiration time. If an item should be added to the list and the key already exists, the values for the expiration time and data of the existing item have to be update within the list. The list has to be reordered based on the new priority of the item as well.

## Use Case
Imagine a distributed web environment where actions in your application have to be taken based on frequently occurring events for the same entities. If these actions are very cost intensive it probably makes sense to execute them only once by buffering the events for a while and fetch the latest scheduled action from the queue as soon as the expiration time has been reached.

### First Approach
This approach is based on a doubly linked list. Inserts have to be done ordered based on the expiration time, so that the item with the lowest expiration time is at the beginning of the list. From there it can be fetched with a _pop()_ operation. When updating an existing item it has to be moved within the list, if the expiration time has changed.
_Advantage_: Very intuitive. No redundant data. Everything can be implement within a list. 
_Disadvantage_: Needs to traverse the list very often when inserting or updating item. Cost intensive in terms of CPU time.

### Second Approach
A PriorityQueue (implemented as _min-heap_), where each item is not necessarily unique, supported by two HashMaps holding the data and the most recent priority for a given key.
When querying the queue for an item to evict the top element of the queue will be checked if its expiration time is equal or lower than a given threshold (current time). To validate if this is the most recent version of the item, the expiration time of the top item from the queue will be compared with the latest known priority for the given key from the HashMap. If this is the the case, the item will be returned from the _evict()_ function, otherwise it will return _null_. In both cases the item will be removed from the queue and from both HashMaps.
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
