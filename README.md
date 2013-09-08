GitMobile
=========

Mobile GIT repository

exploring web bots
under heavy construction

Bot development principles:
1. Win ans *nix compatibility
2. KISS
3. Robust
4. Stealth

Top-down design
===============
Module 1 "Collector" named by source : Get, parse and save data to db
+	html file to string
		how to detect a code page? meta tags, iconv to utf8
		design changes checker - report to master - full page similarity? no. how to check only design?
		Human emulation
			start from main page
			keep session
			give right refferer
			use random timeouts
+	string to array of blocks
+	blocks to array of elements
+	elements to json gzip file

Module 2 "Analyser" m2.php : Form global db, check new data for uniqs, tag and cluster data, do some stats
+	new db to array of blocks
+       if global db exists
+           global db to array of blocks
+           filter uniq blocks
+           cluster uniq blocks
+           add uniq tagged blocks to global
+       if global db does not exist
+           cluster new blocks
                clustering
                    Stage 1 Manual taxonomy (keys, points of interest)
                    	Find taxonomy dictionary
                    	Best way to keep taxonomy? csv - cat:key1,key2
                    Stage 2 For big data sources - automatic clustering
                    	get_words($str){} ---
+           add new blocks to global
+        parse global blocks to category array
+	statistic
+		price statistics, min, max, average, sigma, alert level
	anomaly, optimisation
	alerter
		report all interests
		report anomaly?
	visualisation
		price graphics