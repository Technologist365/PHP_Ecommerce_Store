-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 09, 2011 at 11:13 AM
-- Server version: 5.1.53
-- PHP Version: 5.3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cubecrusher`
--

-- --------------------------------------------------------

--
-- Table structure for table `boxes`
--

CREATE TABLE IF NOT EXISTS `boxes` (
  `box_id` mediumint(3) NOT NULL AUTO_INCREMENT,
  `box_name` varchar(64) NOT NULL DEFAULT '',
  `length` smallint(2) NOT NULL DEFAULT '0',
  `width` smallint(2) NOT NULL DEFAULT '0',
  `height` smallint(2) NOT NULL DEFAULT '0',
  `max_weight` smallint(2) NOT NULL DEFAULT '0',
  `method` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`box_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `boxes`
--


-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE IF NOT EXISTS `carts` (
  `cart_id` int(4) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(3) NOT NULL DEFAULT '0',
  `accessed` bigint(8) NOT NULL DEFAULT '0',
  `purchased` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`cart_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `userid`, `accessed`, `purchased`) VALUES
(1, 3, 1304923486, '0');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `cat_id` smallint(2) NOT NULL AUTO_INCREMENT,
  `owner_id` smallint(2) NOT NULL DEFAULT '0',
  `parent_id` smallint(2) NOT NULL DEFAULT '0',
  `cat_name` varchar(32) NOT NULL DEFAULT '',
  `cat_thumb` varchar(64) NOT NULL DEFAULT '',
  `cat_descrip` mediumtext NOT NULL,
  `long_descrip` mediumtext NOT NULL,
  `sub_cat` enum('0','1') NOT NULL DEFAULT '0',
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `owner_id`, `parent_id`, `cat_name`, `cat_thumb`, `cat_descrip`, `long_descrip`, `sub_cat`, `active`) VALUES
(1, 1, 1, 'Welcome', '', 'Meta Tag Store Description', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is the main store page of the <b>CubeCrusher</b> online movie rental store.', '0', '1'),
(6, 1, 3, 'X-Box', '', '&nbsp;&nbsp;- This is a subcategory of games, games for the X-Box are located in here.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The X-Box category is a subcategory of games, X-Box games for rent are located within this category.', '0', '1'),
(7, 1, 2, 'VHS', '', '&nbsp;&nbsp;- This is a subcategory of videos, videos in Video Home System format are located within this category.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The VHS category is a subcategory of videos, VHSs for rent are located within this category.', '0', '1'),
(8, 1, 2, 'DVD', '', '&nbsp;&nbsp;- This is a subcategory of videos, videos in Digital Video Disc format are located within this category.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The DVD category is a subcategory of videos, DVDs for rent are located within this category.', '0', '1'),
(9, 1, 2, 'Blu-Ray', '', '&nbsp;&nbsp;- This is a subcategory of videos,videos in the BLu-Ray format are located within this category.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Blu-Ray category is a subcategory of videos, Blu-Ray videos for rent are located within this category.', '0', '1'),
(2, 1, 1, 'Movies', '', '&nbsp;- This category is for movies and videos.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This category is for movies and videos and such.', '1', '1'),
(3, 1, 1, 'Games', '', '&nbsp;- This category is for video games.', '&nbsp;&nbsp;&nbsp;&nbsp;This category is for video games.', '1', '1'),
(4, 1, 3, 'PS3', '', '&nbsp;&nbsp;- This is a subcategory of games, PS3 games are located in here.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The PS3 category is a subcategory of games, PS3 games for rent are located within this category.', '0', '1'),
(5, 1, 3, 'Wii', '', '&nbsp;&nbsp;- This is a subcategory of games, games for the Wii are located in here.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Wii category is a subcategory of games, Wii games for rent are located within this category.', '0', '1');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` mediumtext NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `config_enum`
--

CREATE TABLE IF NOT EXISTS `config_enum` (
  `var_name` varchar(32) NOT NULL DEFAULT '',
  `value` enum('0','1','2','3') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config_enum`
--

INSERT INTO `config_enum` (`var_name`, `value`) VALUES
('installed', '1'),
('activation_type', '0'),
('captcha_register', '1'),
('captcha_comment', '1'),
('display_free', '1'),
('display_purchase', '1'),
('make_free', '1'),
('make_purchase', '3');

-- --------------------------------------------------------

--
-- Table structure for table `config_text`
--

CREATE TABLE IF NOT EXISTS `config_text` (
  `var_name` varchar(32) NOT NULL DEFAULT '',
  `value` varchar(64) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config_text`
--

INSERT INTO `config_text` (`var_name`, `value`) VALUES
('account_email', 'registration@egmods.com'),
('admin_email', '$bedfordd@egmods.com'),
('store_url', 'http://www.egmods.com/store/'),
('store_title', 'CubeCrusher ~ Digital Ecommerce Store Script'),
('template', 'default'),
('cubecrusher_version', '0.0.1a');

-- --------------------------------------------------------

--
-- Table structure for table `in_cart`
--

CREATE TABLE IF NOT EXISTS `in_cart` (
  `in_id` int(4) NOT NULL AUTO_INCREMENT,
  `cart_id` int(4) NOT NULL DEFAULT '0',
  `package_id` int(4) NOT NULL DEFAULT '0',
  `prod_id` int(4) NOT NULL DEFAULT '0',
  `prod_price` int(4) NOT NULL DEFAULT '0',
  `prod_qty` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`in_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `in_cart`
--

INSERT INTO `in_cart` (`in_id`, `cart_id`, `package_id`, `prod_id`, `prod_price`, `prod_qty`) VALUES
(4, 1, 0, 1, 1, 1),
(6, 1, 0, 2, 3, 1),
(5, 1, 0, 1, 2, 1),
(9, 1, 0, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE IF NOT EXISTS `licenses` (
  `license_id` int(4) NOT NULL AUTO_INCREMENT,
  `license_name` varchar(64) NOT NULL DEFAULT '',
  `license_text` text NOT NULL,
  `product_id` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`license_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `licenses`
--


-- --------------------------------------------------------

--
-- Table structure for table `methods`
--

CREATE TABLE IF NOT EXISTS `methods` (
  `method_id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `accept_icon` varchar(32) NOT NULL DEFAULT '',
  `checkout_icon` varchar(32) NOT NULL DEFAULT '',
  `account` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `currency` varchar(16) NOT NULL DEFAULT 'USD',
  `token` varchar(64) NOT NULL DEFAULT '',
  `type` enum('0','1','2') NOT NULL DEFAULT '0',
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `live` enum('0','1') NOT NULL DEFAULT '0',
  `live_url` varchar(64) NOT NULL DEFAULT '',
  `test_url` varchar(64) NOT NULL DEFAULT '',
  `directory` varchar(16) NOT NULL DEFAULT '',
  `method_file` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`method_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `methods`
--

INSERT INTO `methods` (`method_id`, `name`, `accept_icon`, `checkout_icon`, `account`, `password`, `currency`, `token`, `type`, `active`, `live`, `live_url`, `test_url`, `directory`, `method_file`) VALUES
(1, 'Paypal', 'paypal_acceptance.gif', 'paypal_checkout.gif', 'bedfor_1218515670_biz@egmods.com', '', 'USD', '750gZWy2Hpw6gxWyHR7EGbtEqBxn-MRNuifpyzBTx_4gGGt2rDT0B9QA6O0', '1', '1', '0', 'https://www.paypal.com', 'https://www.sandbox.paypal.com', 'paypal', 'paypal_checkout.php'),
(2, 'Google Checkout', '', '', '', '', 'USD', '', '0', '0', '0', '', '', '', 'google.php'),
(3, 'USPS', '', '', '', '', 'USD', '', '2', '1', '0', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE IF NOT EXISTS `packages` (
  `package_id` int(4) NOT NULL AUTO_INCREMENT,
  `order_id` int(4) NOT NULL DEFAULT '0',
  `box_id` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `packages`
--


-- --------------------------------------------------------

--
-- Table structure for table `pricing`
--

CREATE TABLE IF NOT EXISTS `pricing` (
  `price_id` int(4) NOT NULL AUTO_INCREMENT,
  `prod_id` int(4) NOT NULL DEFAULT '0',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `filename` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  `type` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `license_id` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`price_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pricing`
--

INSERT INTO `pricing` (`price_id`, `prod_id`, `cost`, `filename`, `name`, `type`, `license_id`) VALUES
(1, 1, '3.00', '', 'VHS', '0', 0),
(2, 1, '4.00', '', 'DVD', '0', 0),
(3, 2, '5.00', '', 'Blu-Ray', '0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_relations`
--

CREATE TABLE IF NOT EXISTS `pricing_relations` (
  `price_id` int(4) NOT NULL,
  `prod_id` int(4) NOT NULL,
  KEY `price_id` (`price_id`),
  KEY `prod_id` (`prod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pricing_relations`
--

INSERT INTO `pricing_relations` (`price_id`, `prod_id`) VALUES
(1, 1),
(2, 1),
(1, 2),
(2, 2),
(3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `prodcat_privs`
--

CREATE TABLE IF NOT EXISTS `prodcat_privs` (
  `priv_id` int(4) NOT NULL AUTO_INCREMENT,
  `user_id` int(4) NOT NULL DEFAULT '0',
  `cat_id` int(4) NOT NULL DEFAULT '0',
  `prod_id` int(4) NOT NULL DEFAULT '0',
  `comment` enum('0','1','2') NOT NULL DEFAULT '0',
  `product` enum('0','1','2') NOT NULL DEFAULT '0',
  `category` enum('0','1','2') NOT NULL DEFAULT '0',
  `gallery` enum('0','1','2') NOT NULL DEFAULT '0',
  `oher_comment` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`priv_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `prodcat_privs`
--


-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `prod_id` int(4) NOT NULL AUTO_INCREMENT,
  `owner_id` int(4) NOT NULL DEFAULT '0',
  `cat_id` int(4) NOT NULL DEFAULT '0',
  `prod_name` varchar(64) NOT NULL DEFAULT '',
  `short_descrip` mediumtext NOT NULL,
  `long_descrip` mediumtext NOT NULL,
  `prod_note` varchar(64) NOT NULL,
  `release_year` smallint(2) NOT NULL DEFAULT '2012',
  `length` smallint(2) NOT NULL DEFAULT '0',
  `width` smallint(2) NOT NULL DEFAULT '0',
  `height` smallint(2) NOT NULL DEFAULT '0',
  `weight` smallint(2) NOT NULL DEFAULT '0',
  `comments` mediumint(3) NOT NULL DEFAULT '0',
  `dl_count` mediumint(4) NOT NULL DEFAULT '-1',
  `downloads` int(4) NOT NULL DEFAULT '0',
  `free_link` varchar(64) NOT NULL DEFAULT '',
  `prod_thumb` varchar(64) NOT NULL DEFAULT '',
  `prod_pic` varchar(64) NOT NULL DEFAULT '',
  `pic_count` smallint(2) NOT NULL DEFAULT '0',
  `free` enum('0','1') NOT NULL DEFAULT '0',
  `active` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`prod_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`prod_id`, `owner_id`, `cat_id`, `prod_name`, `short_descrip`, `long_descrip`, `prod_note`, `release_year`, `length`, `width`, `height`, `weight`, `comments`, `dl_count`, `downloads`, `free_link`, `prod_thumb`, `prod_pic`, `pic_count`, `free`, `active`) VALUES
(1, 1, 4, 'Test Number 1', '&nbsp;&nbsp;-&nbsp;Test Short Description for the test product. You should put some sort of a real description here or something abotu your product. But you should keep it pretty short, or long enough so that it looks decent or something.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. Test Long Description. ', '', 2012, 0, 0, 0, 0, 0, -1, 0, '', 'freemod_thumb.png', 'test_pic.png', 0, '0', '1'),
(2, 1, 4, 'Test Number 2', '&nbsp;&nbsp;-&nbsp;Test Short description number two, describe the product, blah blah blah what it does or something, what it contains, what its about etc. etc. Some more crappy text and junk.', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Test Short description number two, describe the product, blah blah blah what it does or something, what it contains, what its about etc. etc. Some more crappy text and junk.Test Short description number two, describe the product, blah blah blah what it does or something, what it contains, what its about etc. etc. Some more crappy text and junk.Test Short description number two, describe the product, blah blah blah what it does or something, what it contains, what its about etc. etc. Some more crappy text and junk.Test Short description number two, describe the product, blah blah blah what it does or something, what it contains, what its about etc. etc. Some more crappy text and junk.', '', 2012, 0, 0, 0, 0, 0, 5, 0, '', 'env_thumb.png', 'test_pic.png', 0, '0', '1');

-- --------------------------------------------------------

--
-- Table structure for table `product_relations`
--

CREATE TABLE IF NOT EXISTS `product_relations` (
  `cat_id` int(4) NOT NULL,
  `prod_id` int(4) NOT NULL,
  KEY `cat_id` (`cat_id`,`prod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_relations`
--

INSERT INTO `product_relations` (`cat_id`, `prod_id`) VALUES
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `product_rentals`
--

CREATE TABLE IF NOT EXISTS `product_rentals` (
  `rental_id` int(4) NOT NULL AUTO_INCREMENT,
  `prod_id` int(4) NOT NULL,
  `price_id` int(4) NOT NULL,
  `in_out_date` datetime NOT NULL,
  `in_out_status` enum('0','1') NOT NULL DEFAULT '1',
  `last_out_by` int(4) NOT NULL,
  PRIMARY KEY (`rental_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `product_rentals`
--

INSERT INTO `product_rentals` (`rental_id`, `prod_id`, `price_id`, `in_out_date`, `in_out_status`, `last_out_by`) VALUES
(1, 1, 1, '2011-05-09 11:11:48', '0', 3),
(2, 1, 1, '2011-05-09 11:12:02', '0', 3),
(3, 1, 2, '2011-05-09 11:11:48', '0', 3),
(4, 1, 2, '2011-05-09 11:12:02', '0', 3),
(5, 1, 2, '0000-00-00 00:00:00', '1', 0),
(6, 2, 1, '2011-05-09 11:11:48', '0', 3),
(7, 2, 2, '0000-00-00 00:00:00', '1', 0),
(8, 2, 2, '0000-00-00 00:00:00', '1', 0),
(9, 2, 3, '2011-05-09 11:11:48', '0', 3),
(10, 2, 3, '2011-05-09 11:12:02', '0', 3);

-- --------------------------------------------------------

--
-- Table structure for table `prod_pics`
--

CREATE TABLE IF NOT EXISTS `prod_pics` (
  `pic_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `pic_num` mediumint(9) NOT NULL DEFAULT '0',
  `prod_id` int(11) NOT NULL DEFAULT '0',
  `pic_thumb` varchar(64) NOT NULL DEFAULT '',
  `pic_link` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`pic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `prod_pics`
--


-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE IF NOT EXISTS `purchases` (
  `prod_id` int(4) NOT NULL DEFAULT '0',
  `prod_qty` int(4) NOT NULL DEFAULT '0',
  `prod_price` int(4) NOT NULL DEFAULT '0',
  `remain_downloads` int(4) NOT NULL DEFAULT '-1',
  `userid` int(4) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `purchases`
--


-- --------------------------------------------------------

--
-- Table structure for table `rental_history`
--

CREATE TABLE IF NOT EXISTS `rental_history` (
  `history_id` int(4) NOT NULL AUTO_INCREMENT,
  `rental_id` int(4) NOT NULL,
  `prod_id` int(4) NOT NULL,
  `price_id` int(4) NOT NULL,
  `renter_userid` int(4) NOT NULL,
  `checkout_date` datetime NOT NULL,
  `checkin_date` datetime NOT NULL,
  PRIMARY KEY (`history_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `rental_history`
--

INSERT INTO `rental_history` (`history_id`, `rental_id`, `prod_id`, `price_id`, `renter_userid`, `checkout_date`, `checkin_date`) VALUES
(1, 1, 1, 1, 3, '2011-05-09 11:11:48', '0000-00-00 00:00:00'),
(2, 9, 2, 3, 3, '2011-05-09 11:11:48', '0000-00-00 00:00:00'),
(3, 3, 1, 2, 3, '2011-05-09 11:11:48', '0000-00-00 00:00:00'),
(4, 6, 2, 1, 3, '2011-05-09 11:11:48', '0000-00-00 00:00:00'),
(5, 2, 1, 1, 3, '2011-05-09 11:12:02', '0000-00-00 00:00:00'),
(6, 10, 2, 3, 3, '2011-05-09 11:12:02', '0000-00-00 00:00:00'),
(7, 4, 1, 2, 3, '2011-05-09 11:12:02', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `tag_id` smallint(2) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(32) NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tag_id`, `tag_name`) VALUES
(1, 'Action'),
(2, 'Comedy'),
(3, 'Drama'),
(4, 'Horror'),
(5, 'Special');

-- --------------------------------------------------------

--
-- Table structure for table `tag_relations`
--

CREATE TABLE IF NOT EXISTS `tag_relations` (
  `tag_id` smallint(2) NOT NULL,
  `prod_id` int(4) NOT NULL,
  KEY `tag_id` (`tag_id`),
  KEY `prod_id` (`prod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tag_relations`
--


-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `tran_num` int(4) NOT NULL AUTO_INCREMENT,
  `tran_id` varchar(64) NOT NULL DEFAULT '',
  `method_id` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tran_num`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `transactions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(3) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(40) NOT NULL DEFAULT '',
  `username` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(41) DEFAULT NULL,
  `salt` varchar(9) NOT NULL DEFAULT '',
  `downloaded` enum('0','1') NOT NULL DEFAULT '0',
  `first_name` varchar(32) NOT NULL DEFAULT 'John',
  `last_name` varchar(32) NOT NULL DEFAULT 'Doe',
  `phone` varchar(13) NOT NULL,
  `ssn` varchar(12) NOT NULL,
  `website` varchar(64) NOT NULL DEFAULT 'www.egmods.com',
  `activated` enum('0','1') NOT NULL DEFAULT '0',
  `recieve_email` enum('0','1') NOT NULL DEFAULT '1',
  `privs` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `email_address`, `username`, `password`, `salt`, `downloaded`, `first_name`, `last_name`, `phone`, `ssn`, `website`, `activated`, `recieve_email`, `privs`) VALUES
(1, 'bedfordd@egmods.com', 'Spreegem', '6eec2b0d89581f344da6e901660a020fa29ebfc8', 'kexy6er9', '0', 'John', 'Doe', '', '0', 'www.egmods.com', '1', '1', 1),
(3, 'bedfordd@egmods.com', 'bedfordd', '57136f4eef6b282cb5d25fe81f8be6259d501238', 'xhak8mmp', '0', 'David', 'Bedford', '860-716-6209', '333-22-4444', 'www.egmods.com', '1', '1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `address_id` mediumint(3) NOT NULL,
  `userid` mediumint(3) NOT NULL,
  `address_name` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `email` varchar(64) NOT NULL,
  `address1` varchar(128) NOT NULL,
  `address2` varchar(128) NOT NULL,
  `country` varchar(64) NOT NULL,
  `city` varchar(64) NOT NULL,
  `state` varchar(21) NOT NULL,
  `zip` smallint(2) NOT NULL,
  `special` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `userid`, `address_name`, `name`, `phone`, `email`, `address1`, `address2`, `country`, `city`, `state`, `zip`, `special`) VALUES
(0, 3, '', 'DB', '860-716-6209', 'bedfordd@egmods.com', 'Eastern Connecticut State University', '83 Windham St', 'United States', 'Willimantic', 'Connecticut', 6226, '');

-- --------------------------------------------------------

--
-- Table structure for table `user_privs`
--

CREATE TABLE IF NOT EXISTS `user_privs` (
  `priv_id` smallint(2) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '',
  `config` enum('0','1') NOT NULL DEFAULT '0',
  `comment` enum('0','1','2') NOT NULL DEFAULT '0',
  `product` enum('0','1','2') NOT NULL DEFAULT '0',
  `category` enum('0','1','2') NOT NULL DEFAULT '0',
  `user` enum('0','1','2') NOT NULL DEFAULT '0',
  `gallery` enum('0','1','2') NOT NULL DEFAULT '0',
  `other_comment` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`priv_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `user_privs`
--

INSERT INTO `user_privs` (`priv_id`, `title`, `config`, `comment`, `product`, `category`, `user`, `gallery`, `other_comment`) VALUES
(1, 'Administrator', '1', '2', '2', '2', '2', '2', '1'),
(2, 'Super Mod', '0', '2', '1', '1', '1', '1', '1'),
(3, 'Moderator', '0', '1', '0', '0', '0', '0', '1'),
(4, 'User', '0', '1', '0', '0', '0', '0', '0');
