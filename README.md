# Bandcamp Downloader Bundle

## Installation
**Composer**
```bash
composer config repositories.get-repo/bandcamp-downloader-bundle git https://github.com/get-repo/bandcamp-downloader-bundle
composer config repositories.rg3/youtube-dl '{"type":"package","package":{"name":"rg3/youtube-dl","version":"2017.07.23","dist":{"url":"https://github.com/rg3/youtube-dl/archive/2017.07.23.zip","type":"zip"},"source":{"url":"git@github.com:rg3/youtube-dl.git","type":"git","reference":"tags/2017.07.23"}}}'
composer require get-repo/bandcamp-downloader-bundle
```
**Update your `./app/AppKernel.php`**
```php
$bundles = [
    ...
    new GetRepo\BandcampDownloaderBundle\BandcampDownloaderBundle(),
    ...
];
```
or with
```bash
php -r "file_put_contents('./app/AppKernel.php', str_replace('];', \"    new GetRepo\BandcampDownloaderBundle\BandcampDownloaderBundle(),\n        ];\", file_get_contents('./app/AppKernel.php')));"
```

## Command Line
```bash
./vendor/bin/bandcamp-downloader

Usage: bandcamp-downloader [options] <url>
Options:
  -s --save-path   Sve path directory (default: 'save_path' config)
  -h -H --help     This help message
```

## Configuration Reference
```yaml
bandcamp_downloader:
    # Albums save path
    save_path: '%kernel.root_dir%/..'
    # CSS selectors
    selectors:
        artist: 'span[itemprop=byArtist]'
        album: '.trackTitle[itemprop=name]'
        tracks: '#track_table .title a[itemprop=url]'
        cover: '#tralbumArt img[itemprop=image]'
```
