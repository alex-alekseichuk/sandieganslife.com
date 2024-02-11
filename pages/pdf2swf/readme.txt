how to convert pdf to swf for vmag.

we have the page 1.pdf and we need to have correct 1.swf page.


- use command like:
		pdf2swf.exe -s zoom=100 -s jpegquality=100 1.pdf
	to create 1.swf file

- catch the width and height of the page by command:
		swfdump 1.swf >1.txt
	there is in 1.txt file:
		[HEADER]        File version: 8
		[HEADER]        File size: 160663
		[HEADER]        Frame rate: 12.000000
		[HEADER]        Frame count: 1
		[HEADER]        Movie width: 1224.00
		[HEADER]        Movie height: 792.00
	so, 1.swf is 1224x792

- combine 2 swf files into new one with specified width and height by command:
		swfcombine.exe -o 1.sfw --width=1699 --height=1100 template.swf page=1.swf
	be sure to provide widht and height we get on previous step


now we have 1.swf ready to be inserted into vmag

