freepbx-trunk-balancing
=======================

FreePBX module for balancing call load on trunks


Forked from colsolgrp
http://projects.colsolgrp.net/projects/trunkbalance/

This project is freely released under GNU GPL3 license

The following content was in the original file readme.txt 
- Does not work when creating a BAL_trunk linked to a dundi or enum trunk (the macro for dundi and enum are not the same and the call will fail).
- the cdrb query for calls on a dahdi channel will not work (trunk referenced as zap in the trunk database and calls as dahdi in the cdrb).
- the time of the expiration date or the day of billing is in the serveur time zone.


