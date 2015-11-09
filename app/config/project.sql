-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: Nov 17, 2010 as 10:00 PM
-- Versão do Servidor: 5.1.30
-- Versão do PHP: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Banco de Dados: `radiotoca`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_requests`
--

DROP TABLE IF EXISTS `api_requests`;
CREATE TABLE IF NOT EXISTS `api_requests` (
  `REQUEST_ID` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  PRIMARY KEY (`REQUEST_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `api_requests`
--

INSERT INTO `api_requests` (`REQUEST_ID`, `date`) VALUES
(1, '2010-11-17 21:34:24');

-- --------------------------------------------------------

--
-- Estrutura da tabela `listeners`
--

DROP TABLE IF EXISTS `listeners`;
CREATE TABLE IF NOT EXISTS `listeners` (
  `LISTENER_ID` varchar(255) NOT NULL,
  `screen_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `tweets_count` int(11) NOT NULL DEFAULT '0',
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`LISTENER_ID`),
  KEY `screen_name` (`screen_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `listeners`
--

INSERT INTO `listeners` (`LISTENER_ID`, `screen_name`, `name`, `description`, `url`, `location`, `tweets_count`, `image_url`) VALUES
('8515842', 'vinisiller', 'VinÃ­cius Siller', 'motion.designer.web.dev.', 'http://siller.com.br', 'Brasil', 1, 'http://a1.twimg.com/profile_images/1161879993/msn_14_normal.jpg'),
('56391956', 'diegohaz', 'Diego Haz', 'Web designer, ilustrador e gago nas horas vagas. Photoshop, Illustrator, CSS, PHP, AS3.', 'http://diegohaz.com', 'Nova IguaÃ§u - RJ', 3, 'http://a0.twimg.com/profile_images/856147860/orkut3_normal.jpg'),
('21750698', 'war10ck_ti', 'SÃ©rgio Rodrigues', 'desenvolvedor front end viciado em livros, tv, filmes, internet e cafÃ©.', 'http://blog.sergiorodrigues.art.br/', 'JataÃºba - PE, Brazil', 2, 'http://a2.twimg.com/profile_images/1076737414/41422_1450446310_3159_n_normal.jpg'),
('98277845', 'Nandik_Gn', 'Nandik_Gn', '', NULL, 'SALVADOR ', 2, 'http://a2.twimg.com/profile_images/1166429670/imm_2010_11_normal.JPG'),
('170468779', 'SerjaoMiranda', 'Sergio Miranda', '', NULL, 'brasil', 6, 'http://a1.twimg.com/profile_images/1131361137/Sem_t_tulo_normal.png'),
('163195464', 'hostcerta', 'Host Certa', 'A HostCerta, Ã© uma Empresa atuante no mercado de Hospedagem de Sites, Revenda de Hospedagem, Streaming ShoutCast e VPS.', 'http://www.hostcerta.com.br', 'Porto Alegre, Brasil', 1, 'http://a1.twimg.com/profile_images/1065930305/symbol_normal.png'),
('59545426', 'FabrizioZotti', 'Fabrizio Zotti', '.AbraÃ‡o & SorTe ', NULL, 'Salvador - Bahia ', 4, 'http://a3.twimg.com/profile_images/1122023227/after-110920100889_normal.jpg'),
('216340271', 'camilaramooos', 'Mila Ramos', 'Bahia! \\o/', NULL, 'Brasil', 1, 'http://a0.twimg.com/profile_images/1168488644/OQAAAPioF6pAFQ_1BkQTGFgRdsNXB-7Nh2q-WM-Ih5SS7RRfbcIuvKh-olpuagWWkc2Z8ImObE48wx2HuOtiU95HtIcAm1T1UGplG6MtrmB1vXzpPnDXph7RrMjQ_normal.jpg'),
('84148486', 'oliveirasinho', 'Anderson Oliveira', '', NULL, 'Brasil', 5, 'http://a0.twimg.com/profile_images/1109517828/DSC04239_normal.JPG'),
('67509912', 'aninhabibi', 'Ana Beatriz', '', NULL, '', 3, 'http://a2.twimg.com/profile_images/1162037730/dvd_vaid3_papaeventos__38__normal.JPG'),
('59638702', 'Junni0r', 'R. JÃºnior', 'Ariano, Baiano, PublicitÃ¡rio, FotÃ³grafo, Baladeiro, Solteiro, EngraÃ§ado, Chato, Amigo, Teimoso..', NULL, 'Salvador - Ba', 2, 'http://a3.twimg.com/profile_images/1151412331/DSC_0031_normal.JPG'),
('104166785', 'DuduAraujoxD', 'à«¯Ä‘Ï…Î±ÑÄ‘Ïƒ Î±à«¨Î±ÃºjÑ³''', 'Eduardo Araujo Santana .CALDEIRAO (L FERVE TUDO AE o/\r\nBr0thers d0wnloadS Mp3\r\nMELHOR COMUNIDADE http://www.orkut.com.br/Main#Community?cmm=98503222\r\n', 'http://www.orkut.com.br/Main#Profile?uid=6631573071754985788', 'EstÃ¢ncia - Sergipe', 6, 'http://a2.twimg.com/profile_images/1149862882/123_normal.jpg'),
('162845192', 'webcastelo', 'CasteloWeb Digital', 'Temos muito orgulho e grande satisfaÃ§Ã£o de apresenta-lhe a nossa empresa, CASTELOWEB, uma empresa moderna, Ã¡gil, com soluÃ§Ãµes prÃ¡ticas e viÃ¡veis!', 'http://www.casteloweb.com.br', 'Salvador/BA', 2, 'http://a3.twimg.com/profile_images/1168966687/marca_normal.png'),
('170391987', 'flaviagodeiro', 'Flavia Godeiro', '', 'http://www.orkut.com.br/Main#Profile?rl=mp&uid=15917945207985685342', 'Salvador Bahia', 1, 'http://a0.twimg.com/profile_images/1087451292/OgAAAL1JjlORKbJuFgMptxIVKpuNwq1bT6gOZz07OQI_L8kLA3Vd-uaZH39Ga9YsQadBERcqiQztwu-6wxAFrgkJE3gAm1T1UCpptin5AL67Z7rQr-KgICWZQtkq_normal.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `meta`
--

DROP TABLE IF EXISTS `meta`;
CREATE TABLE IF NOT EXISTS `meta` (
  `META_ID` int(11) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`META_ID`),
  UNIQUE KEY `attribute` (`attribute`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `meta`
--

INSERT INTO `meta` (`META_ID`, `attribute`, `value`) VALUES
(1, 'tweets_count', '39'),
(2, 'listeners_count', '14');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tweets`
--

DROP TABLE IF EXISTS `tweets`;
CREATE TABLE IF NOT EXISTS `tweets` (
  `TWEET_ID` varchar(255) NOT NULL,
  `LISTENER_ID` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`TWEET_ID`),
  KEY `LISTENER_ID` (`LISTENER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tweets`
--

INSERT INTO `tweets` (`TWEET_ID`, `LISTENER_ID`, `text`, `created_at`) VALUES
('3175727197323266', '8515842', '<a href="http://twitter.com/diegohaz" target="_blank">@diegohaz</a> Poe um controle de volume na <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a> . Ajuda bem.', '2010-11-12 18:02:07'),
('3160591648690177', '56391956', '<a href="http://twitter.com/war10ck_ti" target="_blank">@war10ck_ti</a> Ã‰ bem eclÃ©tico xD <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-12 17:01:58'),
('3159860053020672', '21750698', '<a href="http://twitter.com/diegohaz" target="_blank">@diegohaz</a> Radical, pulou de 50 Cent pra Maria Gadu.. <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-12 16:59:04'),
('3157689022550016', '21750698', 'Essa mÃºsica me deu um barato. <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-12 16:50:26'),
('3154744176541696', '56391956', 'Testando algumas coisas na <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>. Player funcionando, comunicaÃ§Ã£o com o Twitter ok... vamos ver.', '2010-11-12 16:38:44'),
('2480900331798528', '56391956', 'LanÃ§ando a <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a> essa semana, enquanto isso ouÃ§am: <a href="http://www.radiotoca.com.br" target="_blank">www.radiotoca.com.br</a>', '2010-11-10 20:01:07'),
('2125259138408448', '98277845', '<a href="http://twitter.com/search?q=%23RADIOTOCA" target="_blank">#RADIOTOCA</a> muito bomm', '2010-11-09 20:27:55'),
('4246508497014784', '170468779', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a>  chega mais^^', '2010-11-15 16:57:01'),
('4245321370570753', '163195464', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a>    helow', '2010-11-15 16:52:18'),
('3901846615752704', '59545426', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> BORA BAHIAAAAAAAAAA sempre', '2010-11-14 18:07:27'),
('4606599461085184', '170468779', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a>  o sucesso nao para', '2010-11-16 16:47:53'),
('4606350336204800', '170468779', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> wow', '2010-11-16 16:46:54'),
('4297950511497216', '98277845', 'sÃ³ sucesso <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-15 20:21:25'),
('4625030004609024', '216340271', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a>  Ligadissima sempre, =)', '2010-11-16 18:01:07'),
('4979095469301760', '84148486', 'Banda Eva, â€œFalcÃ£o e os Loucomotivosâ€ e Chica FÃ© no EVANAVE! (11/12) <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-17 17:28:03'),
('4976235662811136', '84148486', 'Radio Toca Informa:Lionel Messi marca, quebra tabu, e Argentina bate SeleÃ§Ã£o em Doha <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>.', '2010-11-17 17:16:41'),
('4884157020774401', '59545426', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> , toca tudo ai, se liga na toca . ABÃ‡', '2010-11-17 11:10:48'),
('4883571458187264', '59545426', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> - Dj monte na parada =)  abraÃ§o', '2010-11-17 11:08:28'),
('4768738788253696', '170468779', 'boa noite atÃ© amanhÃ£ fiquem ligados na <a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> atÃ© +', '2010-11-17 03:32:10'),
('4768658488299521', '67509912', 'E O POVO TÃ QUERENDO MAIS <a href="http://twitter.com/BANDACALDEIRAO" target="_blank">@BANDACALDEIRAO</a> NA <a href="http://twitter.com/search?q=%23RADIOTOCA" target="_blank">#RADIOTOCA</a>', '2010-11-17 03:31:51'),
('4767101944336384', '67509912', 'OLHA A GATA DA ACADEMIAAAAAAAA <a href="http://twitter.com/search?q=%23RADIOTOCA" target="_blank">#RADIOTOCA</a>', '2010-11-17 03:25:40'),
('4767030838296576', '59638702', '<a href="http://twitter.com/BandaCaldeirao" target="_blank">@BandaCaldeirao</a> bombando aqui na <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>, Ferve tudo aÃŠ!', '2010-11-17 03:25:23'),
('4766638461165568', '104166785', 'Amanhaaa tem mais ?? coladinho de novo na <a href="http://twitter.com/search?q=%23radioToca" target="_blank">#radioToca</a> com a <a href="http://twitter.com/bandacaldeirao" target="_blank">@bandacaldeirao</a>', '2010-11-17 03:23:49'),
('4765999018541056', '104166785', 'Nanana nanana nanana nana â™ªâ™ª â™ªâ™ª VocÃª sempre me avisou, Amor larga isso tudo e vamos viver ... RAINHA - Banda CaldeirÃ£o Super nova <a href="http://twitter.com/search?q=%23radioToca" target="_blank">#radioToca</a>', '2010-11-17 03:21:17'),
('4764349864345601', '104166785', 'quer Chorar Ã© Quer chorar Ã© ?? Chore na minhaa â™ª <a href="http://twitter.com/search?q=%23radioToca" target="_blank">#radioToca</a>', '2010-11-17 03:14:44'),
('4763223458840576', '162845192', 'O swingÃ£o da <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a> invadindo a madruga! Sucesssooo!', '2010-11-17 03:10:15'),
('4762551510372352', '104166785', 'Cd de verÃ£o CaldeirÃ£o agora na <a href="http://www.radiotoca.com.br/" target="_blank">http://www.radiotoca.com.br/</a> <a href="http://twitter.com/search?q=%23radioToca" target="_blank">#radioToca</a>', '2010-11-17 03:07:35'),
('4762404491628544', '59638702', 'RT: A <a href="http://twitter.com/search?q=%23RADIOTOCA" target="_blank">#RADIOTOCA</a> FERVENDO COM O CD DE VERÃƒO DA <a href="http://twitter.com/BANDACALDEIRAO" target="_blank">@BANDACALDEIRAO</a> SUCESSO!!!', '2010-11-17 03:07:00'),
('4758739089367040', '67509912', 'A <a href="http://twitter.com/search?q=%23RADIOTOCA" target="_blank">#RADIOTOCA</a> FERVENDO COM O CD DE VERÃƒO DA <a href="http://twitter.com/BANDACALDEIRAO" target="_blank">@BANDACALDEIRAO</a> SUCESSO!!!', '2010-11-17 02:52:26'),
('4757502793089024', '104166785', '<a href="http://twitter.com/search?q=%23radioToca" target="_blank">#radioToca</a> sempre sera sucesso tocando <a href="http://twitter.com/bandacaldeirao" target="_blank">@bandacaldeirao</a>', '2010-11-17 02:47:31'),
('4749915326517248', '104166785', '<a href="http://twitter.com/flaviagodeiro" target="_blank">@flaviagodeiro</a> tamos coladinha com a <a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> '' so pq tem <a href="http://twitter.com/bandacaldeirao" target="_blank">@bandacaldeirao</a>  <a href="http://www.radiotoca.com.br/" target="_blank">http://www.radiotoca.com.br/</a>', '2010-11-17 02:17:22'),
('4745750256820224', '170391987', 'gata da academia na <a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> em breve.', '2010-11-17 02:00:49'),
('4745655809482752', '170468779', '<a href="http://twitter.com/flaviagodeiro" target="_blank">@flaviagodeiro</a> gata da academia na <a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> em breve.', '2010-11-17 02:00:27'),
('4733314007040001', '170468779', 'Todos add no favoritos a <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-17 01:11:24'),
('4723455073198080', '162845192', 'O som da <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a> ta qualidadEE! ÃŠa!!', '2010-11-17 00:32:14'),
('4715246354698242', '84148486', 'Manda um alÃ´ pros amigos da <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a> sempre ouvindo<a href="http://twitter.com/ForroMuido" target="_blank">@ForroMuido</a>', '2010-11-16 23:59:36'),
('4710150409560064', '84148486', 'a radio informa:Corrida de jegue movimenta distrito de Afligidos em SÃ£o GonÃ§alo dos Campos!<a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-16 23:39:22'),
('4708692851171328', '84148486', 'kkkkkkkkkkkkkkkkk... Esse MOnte e um FanfarrÃ£o <a href="http://twitter.com/search?q=%23RadioToca" target="_blank">#RadioToca</a>', '2010-11-16 23:33:34'),
('4686020171796481', '59545426', '<a href="http://twitter.com/search?q=%23radiotoca" target="_blank">#radiotoca</a> dj monte no ar', '2010-11-16 22:03:28');
