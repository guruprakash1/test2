-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2017 at 08:15 PM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `php_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `uniq_id` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(70) NOT NULL,
  `password` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `hobbies` varchar(100) NOT NULL,
  `about_me` varchar(150) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `country` varchar(50) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `last_login` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uniq_id`, `name`, `username`, `email`, `password`, `gender`, `hobbies`, `about_me`, `dob`, `age`, `country`, `mobile`, `photo`, `created`, `last_login`) VALUES
(1, '', 'sbgdshdf', 'xxx', 'prakash.kumarguru@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'male', 'Watching TV--Reading Newspaper', 'jjnbkj', '0000-00-00', 12, 'india', '1144111111111111', '', '2016-09-18 05:20:57', '2016-09-18 05:20:57'),
(2, '', 'sbgdshdf', 'xxx', 'prakash.kumarguru@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', 'male', 'Watching TV--Reading Newspaper', 'jjnbkj', '0000-00-00', 12, 'india', '1144111111111111', '', '2016-09-18 05:20:57', '2016-09-18 05:20:57'),
(3, 'cxvfgjjdcd12255555', 'sbgdshdf', 'xxx12', 'prakash.kumarguru@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'male', 'Playing Cricket', 'jjnbkj', '0000-00-00', 12, 'india', '1144111111111111', '1474739971367.gif', '2016-09-24 19:59:31', '2016-09-24 19:59:31'),
(4, 'd2e50ea5311d481d6933ed3bf4c025fa', 'sjeeee', 'strrerey', 'prakash.kumarguru@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'male', 'Playing Cricket', 'Jai Jagannath Swami', '1991-04-09', 12, 'india', '11445', '1475933233968.jpg', '2016-10-08 15:27:13', '2016-10-08 15:27:13'),
(7, '28815e38d2185b739e8f33a140d51aa2', 'sffergrt', 'xxx1', 'prakash.kumarguru@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'male', 'Reading Newspaper', 'xcfvfb', '1991-04-09', 125, 'bangladesh', '2222222222', '1475949813267.jpg', '2016-10-09 21:47:48', '2016-10-09 21:47:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
