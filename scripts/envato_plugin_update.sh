#!/bin/bash
if [ ! -d site/wp-content/plugins/tmp ]; then
mkdir -p site/wp-content/plugins/tmp;
fi;

if [ -e site/wp-content/plugins/ads-pro/ads-pro-update.zip ] && [ ! -e site/wp-content/plugins/tmp/ads-pro/bsa-plugin-pro-scripteo.zip ]; then
unzip site/wp-content/plugins/ads-pro/ads-pro-update -d site/wp-content/plugins/tmp/ads-pro;
fi;

if [ -d site/wp-content/plugins/ads-pro ] && [ ! -e site/wp-content/plugins/tmp/ads-pro-old.zip ]; then
zip -r site/wp-content/plugins/tmp/ads-pro-old.zip site/wp-content/plugins/ads-pro;
fi;

if [ -e site/wp-content/plugins/tmp/ads-pro/bsa-plugin-pro-scripteo.zip ] && [ -d site/wp-content/plugins/tmp/ads-pro ]; then
unzip site/wp-content/plugins/tmp/ads-pro/bsa-plugin-pro-scripteo.zip -d site/wp-content/plugins/tmp/ads-pro;
fi;

if [ -d site/wp-content/plugins/tmp/ads-pro/bsa-plugin-pro-scripteo ] && [ -d site/wp-content/plugins/ads-pro ]; then
rm -rf site/wp-content/plugins/ads-pro/admin site/wp-content/plugins/ads-pro/admin-ma site/wp-content/plugins/ads-pro/api site/wp-content/plugins/ads-pro/frontend site/wp-content/plugins/ads-pro/lib;
mv site/wp-content/plugins/tmp/ads-pro/bsa-plugin-pro-scripteo/* site/wp-content/plugins/ads-pro/;
fi;

if [ -e site/wp-content/plugins/chimpmate-pro/chimpmate-pro-update.zip ] && [ ! -d site/wp-content/plugins/tmp/chimpmate-pro ]; then
unzip site/wp-content/plugins/chimpmate-pro/chimpmate-pro-update -d site/wp-content/plugins/tmp/chimpmate-pro;
fi;

if [ -d site/wp-content/plugins/chimpmate-pro ] && [ ! -e site/wp-content/plugins/tmp/chimpmate-old.zip ]; then
zip -r site/wp-content/plugins/tmp/chimpmate-old.zip site/wp-content/plugins/chimpmate-pro;
fi;

if [ -d site/wp-content/plugins/tmp/chimpmate-pro/chimpmatepro/ ] && [ -d site/wp-content/plugins/chimpmate-pro ]; then
rm -rf site/wp-content/plugins/chimpmate-pro/admin site/wp-content/plugins/chimpmate-pro/assets site/wp-content/plugins/chimpmate-pro/languages site/wp-content/plugins/chimpmate-pro/public site/wp-content/plugins/chimpmate-pro/src site/wp-content/plugins/chimpmate-pro/widget;
mv site/wp-content/plugins/tmp/chimpmate-pro/chimpmatepro/* site/wp-content/plugins/chimpmate-pro;
fi;

if [ -d site/wp-content/plugins/tmp/chimpmate-pro/Licensing ] && [ -d site/wp-content/plugins/chimpmate-pro/ ]; then
mv site/wp-content/plugins/tmp/chimpmate-pro/Licensing site/wp-content/plugins/chimpmate-pro/;
fi;

if [ -d site/wp-content/plugins/tmp ]; then
rm -rf site/wp-content/plugins/tmp;
fi;
