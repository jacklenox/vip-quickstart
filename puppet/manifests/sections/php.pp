# PHP 5.6 + extensions
include php
include apt

apt::source { 'php56':
  location    => 'http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu',
  release     => 'precise',
  repos       => 'main',
  key         => '14aa40ec0831756756d7f66c4f4ea0aae5267a6c',
  key_server  => 'hkp://keyserver.ubuntu.com:80',
  include_src => true
}

class {
  'php::cli':
    ensure  => latest,
    require => Apt::Source['php56'];
  'php::composer':;
  'php::dev':
    ensure  => latest,
    require => Apt::Source['php56'];
  'php::fpm':;
  'php::pear':;
  'php::phpunit':;

  # Extensions
  'php::extension::curl':;
  'php::extension::gd':;
  'php::extension::imagick':;
  'php::extension::mcrypt':;
  'php::extension::memcache':;
  'php::extension::mysql':;
  'php::extension::xdebug':
    inifile => false,
    settings => [
        'set .anon/xdebug.idekey QUICKSTART',
        'set .anon/xdebug.remote_enable 1',
        'set .anon/xdebug.remote_connect_back 1',
        'set .anon/profiler_enable_trigger 1',
    ];
}

php::fpm::pool { 'www': user => 'www-data' }

# Install PHP_CodeSniffer and the WordPress coding standard
package { 'pear.php.net/PHP_CodeSniffer':
  ensure   => 'installed',
  provider => 'pear',
}

vcsrepo { '/usr/share/php/PHP/CodeSniffer/Standards/WordPress':
  ensure   => 'present',
  source   => 'https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards',
  provider => 'git',
  require  => Package['pear.php.net/PHP_CodeSniffer'],
}

# Turn on html_errors
exec { 'html_errors = On':
  command => 'sed -i "s/html_errors = Off/html_errors = On/g" /etc/php5/fpm/php.ini',
  unless  => 'cat /etc/php5/fpm/php.ini | grep "html_errors = On"',
  user    => root,
  notify  => Service['php5-fpm']
}
