         STRUCTURE
--------------------------------
• documentation  (folder) - Contains source code of bachelor thesis in tex + printable PDF version  
    • chapters   (folder) - tex source code of chapters
    • img        (folder) - images used in documentation
    • literature (folder) - source of literature used in documentation
    • pdf        (folder) - PDF merged with documentation
    • style      (folder) - tex source code of styles used in documentation
    • bakalarka.pdf       - Documentation
    • bakalarka.tex       - main tex source code
    • csplainnatkiv.bst   - tex source code of classes used in literature
    • thesiskiv.tex       - tex source code of used parts in documentation (like abstract, gender etc.)  
    
• module         (folder) - Contains source code of module
    • config     (folder) - contains configuration with data-s used in module (primarly for generator)
    • img        (folder) - images used in evaulation pdf
    • lib        (folder) - third-party libraries used in module (TCPDI + PDF Parser)
    • src        (folder) - PHP source code created by author of bachelor thesis
    • orlib.php           - main file, contains 3 primary functions of module + other minor functions
    
• data           (folder) - Contains examples
    • pres_img   (folder) - Images for presentation
    • evaluation_PDF.pdf  - Example of generated evaluation PDF
    • user_doc.pptx       - Presentation of how to use module on web portal of TSD conference


        INSTALLATION
--------------------------------
To install module on your server, please follow these steps:
    1. Create folder "php" and "php/offline-review" at your server root if they did not exists
    2. Copy module folder into "your_root/php/offline-review" at your server
    3. Open orlib.php and redefine DATA_ROOT for your own root path (default is set in TSD web server configuration)
    

          USER DOC.
--------------------------------
Getting into review page:
    1. Login into TSD conference web page at https://www.kiv.zcu.cz/tsd2019/index.php
    2. Go to "My Reviews"
    3. There, choose one submission to be reviewed and click on Review button
    4. Under text "Offline review form" highlighted by red color, there are 2 buttons:
        4.1 Left one (without green arrow), after clicking this button, module will automatically generate PDF review file
            and download it.
        4.2 Right one (with green arrow), after clicking this button, module will ask you which PDF you want to upload,
            then select the review file and after submitting this file module will extract values and saving it into database,
            and automatically showed extracted values into review form on web evaluating page.
            
Or if you just want to test it on localhost, do this:
    1. Require orlib.php in your code
    2. Somewhere in your code, call method generate_offline_review_form with all parameters, it will generate and download review file
    3. If you want to process file, then do this: 
        3.1 Clear all error logs, replace them with echo
        3.2 Dont call method upload_to_DB_offline_review_form, replace it with foreach and echo to display data
    4. Call method process_offline_review_form with all parameters   