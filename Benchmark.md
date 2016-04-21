# Benchmark
This benchmark shows how 1.000.000 messages are generated for 10.000.000 distinct random keys. The expiration time is set to a random value between 3 and 15 seconds.
  
## Producer
The producer is started with a sleep of 1 microsecond between each message to prevent it from floading the queue.
```
$ php console-producer.php --debug 0 --simulate 1 --keyPrefix mk --keyDistribution 10000000 --numMessages 1000000 --expMinMs 3000000 --expMaxMs 15000000 --sleepUs 1
```

## Queue Server
The server puts the messages on the heap. If there is already a message with the same, which was not evicted before, the new message will replace the old one. This is why we won't have the same amount of evicted objects as we have added.
```
$ php deferred-queue-daemon.php --debug 0  --evictionTickrate 100000
2016-04-20 21:46:39 - [WARN] MemoryUsage:    2 MB.
2016-04-20 21:46:39 - [STATS] Added objects: 0, evictions: 0 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:46:40 - [WARN] MemoryUsage:    6 MB.
2016-04-20 21:46:40 - [STATS] Added objects: 5763, evictions: 0 (5763 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:46:41 - [WARN] MemoryUsage:    16 MB.
2016-04-20 21:46:41 - [STATS] Added objects: 20366, evictions: 0 (14603 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:46:42 - [WARN] MemoryUsage:    28.5 MB.
2016-04-20 21:46:42 - [STATS] Added objects: 34836, evictions: 0 (14470 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:46:43 - [WARN] MemoryUsage:    34.5 MB.
2016-04-20 21:46:43 - [WARN] MemoryPeakUsage 34.5 MB.
2016-04-20 21:46:43 - [STATS] Added objects: 49491, evictions: 107 (14655 Obj/Sec, 107 Evi/Sec).
2016-04-20 21:46:44 - [WARN] MemoryUsage:    42.5 MB.
2016-04-20 21:46:44 - [WARN] MemoryPeakUsage 42.5 MB.
2016-04-20 21:46:44 - [STATS] Added objects: 64047, evictions: 1205 (14556 Obj/Sec, 1098 Evi/Sec).
2016-04-20 21:46:45 - [WARN] MemoryUsage:    53 MB.
2016-04-20 21:46:45 - [WARN] MemoryPeakUsage 53 MB.
2016-04-20 21:46:45 - [STATS] Added objects: 78459, evictions: 3484 (14412 Obj/Sec, 2279 Evi/Sec).
2016-04-20 21:46:46 - [WARN] MemoryUsage:    59 MB.
2016-04-20 21:46:46 - [WARN] MemoryPeakUsage 59 MB.
2016-04-20 21:46:46 - [STATS] Added objects: 92845, evictions: 7031 (14386 Obj/Sec, 3547 Evi/Sec).
2016-04-20 21:46:47 - [WARN] MemoryUsage:    65 MB.
2016-04-20 21:46:47 - [WARN] MemoryPeakUsage 65 MB.
2016-04-20 21:46:47 - [STATS] Added objects: 107074, evictions: 11828 (14229 Obj/Sec, 4797 Evi/Sec).
2016-04-20 21:46:48 - [WARN] MemoryUsage:    69 MB.
2016-04-20 21:46:48 - [WARN] MemoryPeakUsage 69 MB.
2016-04-20 21:46:48 - [STATS] Added objects: 121300, evictions: 17832 (14226 Obj/Sec, 6004 Evi/Sec).
2016-04-20 21:46:49 - [WARN] MemoryUsage:    73 MB.
2016-04-20 21:46:49 - [WARN] MemoryPeakUsage 73 MB.
2016-04-20 21:46:49 - [STATS] Added objects: 135119, evictions: 24947 (13819 Obj/Sec, 7115 Evi/Sec).
2016-04-20 21:46:50 - [WARN] MemoryUsage:    75 MB.
2016-04-20 21:46:50 - [WARN] MemoryPeakUsage 75 MB.
2016-04-20 21:46:50 - [STATS] Added objects: 148725, evictions: 33234 (13606 Obj/Sec, 8287 Evi/Sec).
2016-04-20 21:46:51 - [WARN] MemoryUsage:    77 MB.
2016-04-20 21:46:51 - [WARN] MemoryPeakUsage 77 MB.
2016-04-20 21:46:51 - [STATS] Added objects: 162552, evictions: 42636 (13827 Obj/Sec, 9402 Evi/Sec).
2016-04-20 21:46:52 - [WARN] MemoryUsage:    79 MB.
2016-04-20 21:46:52 - [WARN] MemoryPeakUsage 79 MB.
2016-04-20 21:46:52 - [STATS] Added objects: 175959, evictions: 53255 (13407 Obj/Sec, 10619 Evi/Sec).
2016-04-20 21:46:53 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:53 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:53 - [STATS] Added objects: 189366, evictions: 64680 (13407 Obj/Sec, 11425 Evi/Sec).
2016-04-20 21:46:54 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:54 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:54 - [STATS] Added objects: 202643, evictions: 77620 (13277 Obj/Sec, 12940 Evi/Sec).
2016-04-20 21:46:55 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:55 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:55 - [STATS] Added objects: 215844, evictions: 91292 (13201 Obj/Sec, 13672 Evi/Sec).
2016-04-20 21:46:56 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:56 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:56 - [STATS] Added objects: 229145, evictions: 105225 (13301 Obj/Sec, 13933 Evi/Sec).
2016-04-20 21:46:57 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:57 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:57 - [STATS] Added objects: 242230, evictions: 119024 (13085 Obj/Sec, 13799 Evi/Sec).
2016-04-20 21:46:58 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:58 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:58 - [STATS] Added objects: 254767, evictions: 132735 (12537 Obj/Sec, 13711 Evi/Sec).
2016-04-20 21:46:59 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:46:59 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:46:59 - [STATS] Added objects: 267852, evictions: 146601 (13085 Obj/Sec, 13866 Evi/Sec).
2016-04-20 21:47:00 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:00 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:00 - [STATS] Added objects: 281255, evictions: 160084 (13403 Obj/Sec, 13483 Evi/Sec).
2016-04-20 21:47:01 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:01 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:01 - [STATS] Added objects: 293882, evictions: 173522 (12627 Obj/Sec, 13438 Evi/Sec).
2016-04-20 21:47:02 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:02 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:02 - [STATS] Added objects: 306947, evictions: 186776 (13065 Obj/Sec, 13254 Evi/Sec).
2016-04-20 21:47:03 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:03 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:03 - [STATS] Added objects: 320078, evictions: 199946 (13131 Obj/Sec, 13170 Evi/Sec).
2016-04-20 21:47:04 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:04 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:04 - [STATS] Added objects: 331970, evictions: 213127 (11892 Obj/Sec, 13181 Evi/Sec).
2016-04-20 21:47:05 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:05 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:05 - [STATS] Added objects: 345377, evictions: 226187 (13407 Obj/Sec, 13060 Evi/Sec).
2016-04-20 21:47:06 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:06 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:06 - [STATS] Added objects: 358019, evictions: 239232 (12642 Obj/Sec, 13045 Evi/Sec).
2016-04-20 21:47:07 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:07 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:07 - [STATS] Added objects: 371029, evictions: 252142 (13010 Obj/Sec, 12910 Evi/Sec).
2016-04-20 21:47:08 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:08 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:08 - [STATS] Added objects: 383993, evictions: 264988 (12964 Obj/Sec, 12846 Evi/Sec).
2016-04-20 21:47:09 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:09 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:09 - [STATS] Added objects: 396681, evictions: 277670 (12688 Obj/Sec, 12682 Evi/Sec).
2016-04-20 21:47:10 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:10 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:10 - [STATS] Added objects: 409632, evictions: 290636 (12951 Obj/Sec, 12966 Evi/Sec).
2016-04-20 21:47:11 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:11 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:11 - [STATS] Added objects: 422714, evictions: 303256 (13082 Obj/Sec, 12620 Evi/Sec).
2016-04-20 21:47:12 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:12 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:12 - [STATS] Added objects: 435831, evictions: 316076 (13117 Obj/Sec, 12820 Evi/Sec).
2016-04-20 21:47:13 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:13 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:13 - [STATS] Added objects: 447662, evictions: 328682 (11831 Obj/Sec, 12606 Evi/Sec).
2016-04-20 21:47:14 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:14 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:14 - [STATS] Added objects: 460819, evictions: 341404 (13157 Obj/Sec, 12722 Evi/Sec).
2016-04-20 21:47:15 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:15 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:15 - [STATS] Added objects: 473272, evictions: 354262 (12453 Obj/Sec, 12858 Evi/Sec).
2016-04-20 21:47:16 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:16 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:16 - [STATS] Added objects: 486583, evictions: 367078 (13311 Obj/Sec, 12816 Evi/Sec).
2016-04-20 21:47:17 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:17 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:17 - [STATS] Added objects: 499789, evictions: 379600 (13206 Obj/Sec, 12522 Evi/Sec).
2016-04-20 21:47:18 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:18 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:18 - [STATS] Added objects: 513235, evictions: 392114 (13446 Obj/Sec, 12514 Evi/Sec).
2016-04-20 21:47:19 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:19 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:19 - [STATS] Added objects: 526513, evictions: 404707 (13278 Obj/Sec, 12593 Evi/Sec).
2016-04-20 21:47:20 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:20 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:20 - [STATS] Added objects: 540194, evictions: 417369 (13681 Obj/Sec, 12662 Evi/Sec).
2016-04-20 21:47:21 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:21 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:21 - [STATS] Added objects: 553605, evictions: 430129 (13411 Obj/Sec, 12760 Evi/Sec).
2016-04-20 21:47:22 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:22 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:22 - [STATS] Added objects: 566871, evictions: 443021 (13266 Obj/Sec, 12892 Evi/Sec).
2016-04-20 21:47:23 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:23 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:23 - [STATS] Added objects: 580205, evictions: 455884 (13334 Obj/Sec, 12863 Evi/Sec).
2016-04-20 21:47:24 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:24 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:24 - [STATS] Added objects: 593509, evictions: 468866 (13304 Obj/Sec, 12982 Evi/Sec).
2016-04-20 21:47:25 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:25 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:25 - [STATS] Added objects: 606962, evictions: 481740 (13453 Obj/Sec, 12874 Evi/Sec).
2016-04-20 21:47:26 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:26 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:26 - [STATS] Added objects: 620619, evictions: 494834 (13657 Obj/Sec, 13094 Evi/Sec).
2016-04-20 21:47:27 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:27 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:27 - [STATS] Added objects: 634180, evictions: 507805 (13561 Obj/Sec, 12971 Evi/Sec).
2016-04-20 21:47:28 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:28 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:28 - [STATS] Added objects: 647840, evictions: 520873 (13660 Obj/Sec, 13068 Evi/Sec).
2016-04-20 21:47:29 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:29 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:29 - [STATS] Added objects: 661206, evictions: 534081 (13366 Obj/Sec, 13208 Evi/Sec).
2016-04-20 21:47:30 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:30 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:30 - [STATS] Added objects: 674322, evictions: 547148 (13116 Obj/Sec, 13067 Evi/Sec).
2016-04-20 21:47:31 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:31 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:31 - [STATS] Added objects: 687652, evictions: 560470 (13330 Obj/Sec, 13322 Evi/Sec).
2016-04-20 21:47:32 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:32 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:32 - [STATS] Added objects: 701042, evictions: 573620 (13390 Obj/Sec, 13150 Evi/Sec).
2016-04-20 21:47:33 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:33 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:33 - [STATS] Added objects: 714283, evictions: 586919 (13241 Obj/Sec, 13299 Evi/Sec).
2016-04-20 21:47:34 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:34 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:34 - [STATS] Added objects: 727953, evictions: 600219 (13670 Obj/Sec, 13300 Evi/Sec).
2016-04-20 21:47:35 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:35 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:35 - [STATS] Added objects: 741477, evictions: 613547 (13524 Obj/Sec, 13328 Evi/Sec).
2016-04-20 21:47:36 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:36 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:36 - [STATS] Added objects: 755101, evictions: 626693 (13624 Obj/Sec, 13146 Evi/Sec).
2016-04-20 21:47:37 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:37 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:37 - [STATS] Added objects: 768547, evictions: 639694 (13446 Obj/Sec, 13001 Evi/Sec).
2016-04-20 21:47:38 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:38 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:38 - [STATS] Added objects: 781753, evictions: 652950 (13206 Obj/Sec, 13256 Evi/Sec).
2016-04-20 21:47:39 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:39 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:39 - [STATS] Added objects: 794548, evictions: 666531 (12795 Obj/Sec, 13581 Evi/Sec).
2016-04-20 21:47:40 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:40 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:40 - [STATS] Added objects: 807563, evictions: 679880 (13015 Obj/Sec, 13349 Evi/Sec).
2016-04-20 21:47:41 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:41 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:41 - [STATS] Added objects: 820855, evictions: 693082 (13292 Obj/Sec, 13202 Evi/Sec).
2016-04-20 21:47:42 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:42 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:42 - [STATS] Added objects: 834388, evictions: 706202 (13533 Obj/Sec, 13120 Evi/Sec).
2016-04-20 21:47:43 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:43 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:43 - [STATS] Added objects: 847986, evictions: 719229 (13598 Obj/Sec, 13027 Evi/Sec).
2016-04-20 21:47:44 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:44 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:44 - [STATS] Added objects: 859779, evictions: 732447 (11793 Obj/Sec, 13218 Evi/Sec).
2016-04-20 21:47:45 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:45 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:45 - [STATS] Added objects: 871877, evictions: 745600 (12098 Obj/Sec, 13153 Evi/Sec).
2016-04-20 21:47:46 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:46 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:46 - [STATS] Added objects: 884026, evictions: 758811 (12149 Obj/Sec, 13211 Evi/Sec).
2016-04-20 21:47:47 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:47 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:47 - [STATS] Added objects: 897204, evictions: 772146 (13178 Obj/Sec, 13335 Evi/Sec).
2016-04-20 21:47:48 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:48 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:48 - [STATS] Added objects: 910525, evictions: 785115 (13321 Obj/Sec, 12969 Evi/Sec).
2016-04-20 21:47:49 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:49 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:49 - [STATS] Added objects: 922741, evictions: 797273 (12216 Obj/Sec, 12158 Evi/Sec).
2016-04-20 21:47:50 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:50 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:50 - [STATS] Added objects: 932952, evictions: 807865 (10211 Obj/Sec, 10592 Evi/Sec).
2016-04-20 21:47:51 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:51 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:51 - [STATS] Added objects: 943175, evictions: 818675 (10223 Obj/Sec, 10810 Evi/Sec).
2016-04-20 21:47:52 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:52 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:52 - [STATS] Added objects: 953392, evictions: 829340 (10217 Obj/Sec, 10665 Evi/Sec).
2016-04-20 21:47:53 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:53 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:53 - [STATS] Added objects: 964234, evictions: 841163 (10842 Obj/Sec, 11823 Evi/Sec).
2016-04-20 21:47:54 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:54 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:54 - [STATS] Added objects: 976099, evictions: 855970 (11865 Obj/Sec, 14807 Evi/Sec).
2016-04-20 21:47:55 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:55 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:55 - [STATS] Added objects: 988207, evictions: 870964 (12108 Obj/Sec, 14994 Evi/Sec).
2016-04-20 21:47:56 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:56 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:56 - [STATS] Added objects: 1000000, evictions: 885722 (11793 Obj/Sec, 14758 Evi/Sec).
2016-04-20 21:47:57 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:57 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:57 - [STATS] Added objects: 1000000, evictions: 897417 (0 Obj/Sec, 11695 Evi/Sec).
2016-04-20 21:47:58 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:58 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:58 - [STATS] Added objects: 1000000, evictions: 909012 (0 Obj/Sec, 11595 Evi/Sec).
2016-04-20 21:47:59 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:47:59 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:47:59 - [STATS] Added objects: 1000000, evictions: 920618 (0 Obj/Sec, 11606 Evi/Sec).
2016-04-20 21:48:00 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:00 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:00 - [STATS] Added objects: 1000000, evictions: 931657 (0 Obj/Sec, 11039 Evi/Sec).
2016-04-20 21:48:01 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:01 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:01 - [STATS] Added objects: 1000000, evictions: 941879 (0 Obj/Sec, 10222 Evi/Sec).
2016-04-20 21:48:02 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:02 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:02 - [STATS] Added objects: 1000000, evictions: 950861 (0 Obj/Sec, 8982 Evi/Sec).
2016-04-20 21:48:03 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:03 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:03 - [STATS] Added objects: 1000000, evictions: 958862 (0 Obj/Sec, 8001 Evi/Sec).
2016-04-20 21:48:04 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:04 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:04 - [STATS] Added objects: 1000000, evictions: 965696 (0 Obj/Sec, 6834 Evi/Sec).
2016-04-20 21:48:05 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:05 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:05 - [STATS] Added objects: 1000000, evictions: 971633 (0 Obj/Sec, 5937 Evi/Sec).
2016-04-20 21:48:06 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:06 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:06 - [STATS] Added objects: 1000000, evictions: 976728 (0 Obj/Sec, 5095 Evi/Sec).
2016-04-20 21:48:07 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:07 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:07 - [STATS] Added objects: 1000000, evictions: 981030 (0 Obj/Sec, 4302 Evi/Sec).
2016-04-20 21:48:08 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:08 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:08 - [STATS] Added objects: 1000000, evictions: 984524 (0 Obj/Sec, 3494 Evi/Sec).
2016-04-20 21:48:09 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:09 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:09 - [STATS] Added objects: 1000000, evictions: 987042 (0 Obj/Sec, 2518 Evi/Sec).
2016-04-20 21:48:10 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:10 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:10 - [STATS] Added objects: 1000000, evictions: 988543 (0 Obj/Sec, 1501 Evi/Sec).
2016-04-20 21:48:11 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:11 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:11 - [STATS] Added objects: 1000000, evictions: 989034 (0 Obj/Sec, 491 Evi/Sec).
2016-04-20 21:48:12 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:12 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:12 - [STATS] Added objects: 1000000, evictions: 989034 (0 Obj/Sec, 0 Evi/Sec).
2016-04-20 21:48:13 - [WARN] MemoryUsage:    81 MB.
2016-04-20 21:48:13 - [WARN] MemoryPeakUsage 81 MB.
2016-04-20 21:48:13 - [STATS] Added objects: 1000000, evictions: 989034 (0 Obj/Sec, 0 Evi/Sec).
```