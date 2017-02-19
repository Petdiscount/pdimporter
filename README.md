# Petdiscount Magento 1.x importer

In addition to our API, we also offer a "proof of concept" Magento 1.x importer. It's not an extension but a standalone import script.

View the demo enviroment at: https://magento1.petdiscount.nl

## Install

Installation is quite easy! Just follow the steps:

### Step 1:

Download the ZIP file and move the "pdimporter-master" folder in your Magento root.
Rename "pdimporter-master" to "pdimporter".

Or:
Copy all the files into /pdimporter

Or use:
git clone https://github.com/petdiscount/pdimporter
in your magento root


### Step 2:

Go to your favorite webbrowser and run https://yourmagentourl.com/pdimporter/install.php, fill in your API credentials and answer the questions. This will generate a config file and creates the needed attributes.

### Step 3:

Open your favorite SSH client and "cd" into the pdimporter directory. Run the following command "php cron full" to start the initial import. This takes about 5 to 10 minutes.

### Step 4:

Setup a cronjob for the "stock" and "full" import.

You need to run "php cron stock" every 30 minutes You need to run "php cron full" every day, ideally between 18:00 and 08:00.
