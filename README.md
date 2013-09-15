WebBots
=========

WebBots repository
Exploring web bots for fun and profit

Bot development principles:
1. Win ans *nix compatibility
2. KISS
3. Robust
4. Stealth

Flow
====
Urler
repeat: M1 -> M2 
M3
M4

Data structures
===============
block[] imgs[]
        links[]
        clear_text
        price
        hash
        tags[]

clusters[]  category    size
                        sum
                        average
                        standard_deviation
                        low_limit
                        hi_limit
                        hashes[]
                        prices[]

interests[] category[]  price_level

Top-down design
===============
Module 1 "Collector" named by source : Get, parse and save data to db
+	html file to string
		how to detect a code page? http headers
		design changes checker - report to master - full page similarity? no. 
                    how to check only design?
		Human emulation
                    start from main page
                    keep session
                    give right refferer
                    use random timeouts
+	string to array of blocks
+	blocks to array of elements
+	elements to json gzip file

Module 2 "Filter" filter.php: Form global db, check new data for uniqs and save to global db
+	new db to array of blocks
+       if global db exists
+           global db to array of blocks
+           filter uniq blocks
+           add uniq blocks to global
+       if global db does not exist
+           add new blocks to global

Module 3 Tagging and Clustering   
+           cluster new blocks
                clustering
                    Stage 1 Manual taxonomy (keys, points of interest)
                    	Find taxonomy dictionary
                    	Best way to keep taxonomy? csv - cat:key1,key2
                    Stage 2 For big data sources - automatic clustering
                    	get_words($str){} ---

parse global blocks to category array    
+	statistic
+		price statistics, min, max, average, sigma, alert level
	anomaly, optimisation
	alerter
		report all interests
		report anomaly?
	visualisation
		price graphics

Module 4 Reporter
    Show stats for each category
    Show all blocks in a category with price lower some value - 1 sigma?
    Report to email new blocks with price lower some value

Directory Structure
===================
.htaccess
robots.txt
db - json files, writable by root
libs - php libs
tpl - html templates
bot - web bot php scripts
tmp

WebBots Ideas
=============

Broken link checker