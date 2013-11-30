
Starmade recipe guide generator.

This is PHP script what takes XML Starmade server config file and generate user-friendly guides for better understanding of recipes craft modification.
Script generate 2 HTML files, one just redraw recipes, with repice prices and currency and count of every item what participate in it (this must be best for mod balancing),
second file show in what repices certain block used as both input and output(this one must be best for building recipes chains).

Script need not compressed server's block config XML file.
Config file must have information about all items as script use this info to get block`s names from IDs in recipes.

Bug: script can to not find names for blocks if there is more then 2 levels in blocks categories.
     
Code not best, and still can have unneeded and be not optimal.


Script is free for use "AS IS", please make credits to author.

