
-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Feb 19, 2019 alle 20:08
-- Versione del server: 10.1.24-MariaDB
-- Versione PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `u971260997_ninja`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cncat_cat`
--

CREATE TABLE IF NOT EXISTS `cncat_cat` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `parent` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

--
-- Dump dei dati per la tabella `cncat_cat`
--

INSERT INTO `cncat_cat` (`cid`, `name`, `parent`, `count`) VALUES
(1, 'Computer', 0, 7),
(2, 'Programmazione', 1, 1),
(3, 'Files Utili', 2, 1),
(4, 'Sport', 0, 14),
(6, 'Volley', 4, 10),
(7, 'maschile', 6, 0),
(8, 'femminile', 6, 2),
(20, 'Moda', 10, 0),
(9, 'Internet', 1, 5),
(10, 'Shopping', 0, 1),
(16, 'Accessori', 15, 1),
(12, 'Tempo Libero', 0, 11),
(13, 'Spettacoli', 12, 2),
(15, 'Auto e Motori', 0, 1),
(14, 'MountainBike', 4, 3),
(17, 'Assicurazioni', 15, 0),
(18, 'Cinema', 12, 5),
(19, 'Musica', 12, 2),
(21, 'Arredamento', 10, 0),
(22, 'Hi-Fi', 10, 0),
(23, 'Società', 0, 4),
(24, 'Disabili', 23, 0),
(25, 'Politica', 23, 0),
(26, 'Religione', 23, 1),
(27, 'Viaggi', 0, 1),
(28, 'Hotel', 27, 1),
(29, 'Meteo', 27, 0),
(30, 'Destinazioni', 27, 0),
(31, 'Cultura', 0, 4),
(32, 'Arte', 31, 2),
(33, 'Libri', 31, 0),
(34, 'Istruzione', 31, 0),
(35, 'Attori Attrici', 18, 2),
(36, 'Informazione', 0, 1),
(37, 'Quotidiani', 36, 1),
(38, 'Radio', 36, 0),
(39, 'Tv', 36, 0),
(40, 'Affari e Finanza', 0, 0),
(45, 'Giochi', 12, 2),
(42, 'Banche', 40, 0),
(43, 'Borsa', 40, 0),
(44, 'Lavoro', 40, 0),
(46, 'Salute', 0, 2),
(47, 'Alternativa', 46, 1),
(48, 'Ospedali', 46, 0),
(49, 'Riviste', 46, 0),
(50, 'Scienze', 0, 1),
(51, 'Ricerca', 50, 0),
(52, 'Umane', 50, 0),
(53, 'Alternative', 50, 0),
(54, 'Pubbliche Istituzioni', 0, 0),
(55, 'Ministeri', 54, 0),
(56, 'Sindacati', 54, 0),
(57, 'Partiti Politici', 54, 0),
(58, 'Aree Geografiche', 0, 3),
(59, 'Italia', 58, 3),
(60, 'Europa', 58, 0),
(61, 'Regioni del Mondo', 58, 0),
(62, 'Animali', 12, 0),
(63, 'Cucina', 12, 0),
(64, 'Casa e Giardino', 12, 0),
(65, 'Amatoriale', 6, 0),
(66, 'Blog', 9, 0),
(67, 'Servizi', 0, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `cncat_cat_linear`
--

CREATE TABLE IF NOT EXISTS `cncat_cat_linear` (
  `cid` int(11) DEFAULT NULL,
  `name` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `cncat_cat_linear`
--

INSERT INTO `cncat_cat_linear` (`cid`, `name`) VALUES
(1, 'Computer'),
(2, 'Computer ::: Programmazione'),
(3, 'Computer ::: Programmazione ::: Files Utili'),
(4, 'Sport'),
(6, 'Sport ::: Volley'),
(7, 'Sport ::: Volley ::: maschile'),
(8, 'Sport ::: Volley ::: femminile'),
(9, 'Computer ::: Internet'),
(10, 'Shopping'),
(15, 'Auto e Motori'),
(12, 'Tempo Libero'),
(13, 'Tempo Libero ::: Spettacoli'),
(14, 'Sport ::: MountainBike'),
(16, 'Auto e Motori ::: Accessori'),
(17, 'Auto e Motori ::: Assicurazioni'),
(18, 'Tempo Libero ::: Cinema'),
(19, 'Tempo Libero ::: Musica'),
(20, 'Shopping ::: Moda'),
(21, 'Shopping ::: Arredamento'),
(22, 'Shopping ::: Hi-Fi'),
(23, 'Società'),
(24, 'Società ::: Disabili'),
(25, 'Società ::: Politica'),
(26, 'Società ::: Religione'),
(27, 'Viaggi'),
(28, 'Viaggi ::: Hotel'),
(29, 'Viaggi ::: Meteo'),
(30, 'Viaggi ::: Destinazioni'),
(31, 'Cultura'),
(32, 'Cultura ::: Arte'),
(33, 'Cultura ::: Libri'),
(34, 'Cultura ::: Istruzione'),
(35, 'Tempo Libero ::: Cinema ::: Attori Attrici'),
(36, 'Informazione'),
(37, 'Informazione ::: Quotidiani'),
(38, 'Informazione ::: Radio'),
(39, 'Informazione ::: Tv'),
(40, 'Affari e Finanza'),
(45, 'Tempo Libero ::: Giochi'),
(42, 'Affari e Finanza ::: Banche'),
(43, 'Affari e Finanza ::: Borsa'),
(44, 'Affari e Finanza ::: Lavoro'),
(46, 'Salute'),
(47, 'Salute ::: Alternativa'),
(48, 'Salute ::: Ospedali'),
(49, 'Salute ::: Riviste'),
(50, 'Scienze'),
(51, 'Scienze ::: Ricerca'),
(52, 'Scienze ::: Umane'),
(53, 'Scienze ::: Alternative'),
(54, 'Pubbliche Istituzioni'),
(55, 'Pubbliche Istituzioni ::: Ministeri'),
(56, 'Pubbliche Istituzioni ::: Sindacati'),
(57, 'Pubbliche Istituzioni ::: Partiti Politici'),
(58, 'Aree Geografiche'),
(59, 'Aree Geografiche ::: Italia'),
(60, 'Aree Geografiche ::: Europa'),
(61, 'Aree Geografiche ::: Regioni del Mondo'),
(62, 'Tempo Libero ::: Animali'),
(63, 'Tempo Libero ::: Cucina'),
(64, 'Tempo Libero ::: Casa e Giardino'),
(65, 'Sport ::: Volley ::: Amatoriale'),
(66, 'Computer ::: Internet ::: Blog');

-- --------------------------------------------------------

--
-- Struttura della tabella `cncat_main`
--

CREATE TABLE IF NOT EXISTS `cncat_main` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `description` text,
  `url` text,
  `cat1` int(11) DEFAULT NULL,
  `gin` int(11) DEFAULT NULL,
  `gout` int(11) DEFAULT NULL,
  `moder_vote` int(11) DEFAULT NULL,
  `email` text,
  `type` int(11) DEFAULT NULL,
  `broken` int(11) DEFAULT '0',
  `insert_date` datetime DEFAULT NULL,
  `resfield1` text,
  `resfield2` text,
  `resfield3` text,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=73 ;

--
-- Dump dei dati per la tabella `cncat_main`
--

INSERT INTO `cncat_main` (`lid`, `title`, `description`, `url`, `cat1`, `gin`, `gout`, `moder_vote`, `email`, `type`, `broken`, `insert_date`, `resfield1`, `resfield2`, `resfield3`) VALUES
(71, 'Arredatore e interior designer a Vicenza, Padova e Verona', 'Progettazione arredamenti d&#039;interni, mobili d&#039;antiquariato e design a Vicenza, Padova e Verona by Anna Checchini', 'http://www.arredareconstile.it', 21, NULL, NULL, 0, 'emanuel.righetto@tin.it', 1, 0, '2016-09-04 06:02:27', NULL, NULL, NULL),
(72, 'Consulente Web Marketing | strategie SEO/SEM/DEM', 'Una persona che ti può aiutare nelle tue attività di digital marketing', 'http://www.marketingonweb.it', 67, NULL, NULL, NULL, NULL, NULL, 0, '2016-09-04 08:16:03', NULL, NULL, NULL),
(4, 'Sambo Gomme - Yokohama Pneumatici', 'Sambo Gomme - Rivenditore Ufficiale Pneumatici Yokohama:\r\nAmmortizzatori, Assetto Ruote, Bilanciatura Elettronica Completa, Consulente di Guida, Controllo Elettronico, Preparazioni Sportive', 'http://www.sambogomme.com/', 16, 1, 708, 6, 'emanuel.righetto@tin.it', 1, 0, '2003-11-16 18:17:29', '', '', ''),
(6, 'Biemme Sport - Abbigliamento Sportivo', 'L&#39; azienda vicentina che produce materiale tecnico di alta qualità a bassi costi.', 'http://www.biemmesport.com/', 4, 1, 198, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-11-16 18:47:04', '', '', ''),
(9, 'ALIAS Italia - Il dossier Sydney Bristow', 'Tutto sulla serie ALIAS trasmessa in Italia da RaiDue', 'http://www.antoniogenna.net/alias/alias.htm', 18, 1, 345, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-11-17 20:46:16', '', '', ''),
(11, 'Alberghi in Italia', 'Il portale tutto veronese per la prenotazione on line di Alberghi, Bed & Breakfast, Residence, Agriturismi in Italia', 'http://www.vacans.it/', 28, 1, 214, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-11-18 20:00:13', '', '', ''),
(12, 'Trova Prezzi', 'Il motore di ricerca per i tuoi acquisti! Trova e confronta i migliori prezzi online! ', 'http://www.trovaprezzi.it/', 10, 1, 185, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-11-20 13:15:28', '', '', ''),
(14, 'ComeFunziona.Net - Il sito che vi spiega come funzionano le cose.', 'Hai dei problemi con la tecnologia?\r\nCome funziona ti svela i segreti del tuo pc e dei suoi accessori, scanner, stampanti, periferiche, fotocamere digitali, masterizzatori...', 'http://www.comefunziona.net/', 1, 1, 1237, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-02 19:47:20', '', '', ''),
(22, 'Benessere.com', 'Benessere: alimentazione, dietetica, fitness e sport, salute, psicologia,\r\nsessuologia, terme: oltre 3000 pagine web di informazione su tutti gli aspetti del benessere, moduli interattivi personalizzati, news ogni ora, guida alle Terme e agli Hotel del benessere, dibattiti nel forum, risposte alle tue domande e ...shopping on-line.\r\n', 'http://www.benessere.com/', 46, 1, 125, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-28 09:48:06', '', '', ''),
(23, 'Altopiano di Asiago 7 comuni - 365 giorni di vita all&#39;anno', 'Una guida completa alle offerte di questo luogo storico.\r\nEscursioni Mountain bike Equitazione Bungee jumping Golf Pattinaggio Piste di sci Scuole di sci.\r\nAlberghi Musei Folklore Storia. \r\n \r\n', 'http://www.altopiano-asiago.it/', 59, 1, 3959, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-28 09:53:18', '', '', ''),
(24, 'MV - il Veneto che non hai mai visto...', 'agico Veneto Montagna Veneta ... e dintorni, turismo, foto, fotografie, mountain bike, cicloturismo, escursioni, trekking, alpinismo, sci, speleologia, canoa, geologia, fotografia, natura, sport, arte, storia. Itinerari, informazioni e curiosità, e altro ancora, principalmente nelle Dolomiti e nelle montagne del Veneto, paesi e città del Veneto del Trentino e del Friuli', 'http://www.magicoveneto.it/', 59, 1, 637, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-28 09:57:43', '', '', ''),
(25, 'CultFrame - Arti visive e comunicazione', 'Rivista dell&#39;immagine con sezioni di fotografia, spot e videoclip.  Tutto su mostre, recensioni spot e videoclip, libri di fotografia e pubblicità, maestri della fotografia, fotografia e altre arti, librerie, news, rassegna stampa, credit, link', 'http://www.cultframe.com', 32, 1, 330, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-30 19:38:44', '', '', ''),
(27, 'Il barbiere della sera', 'Il Barbiere della Sera non è una testata giornalistica. E’ solo un crocevia di informazioni e discussione sull’informazione nel nostro Paese, diventato un sicuro punto di riferimento del giornalismo italiano.. \r\n\r\n', 'http://www.ilbarbieredellasera.com', 37, 1, 213, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-30 20:52:50', '', '', ''),
(28, 'La Comunità di Sant’Egidio', 'La Comunità di Sant’Egidio nasce a Roma nel 1968, all’indomani del Concilio Vaticano II. Oggi è un movimento di laici a cui aderiscono più di 40.000 persone, impegnato nella comunicazione del Vangelo e nella carità a Roma, in Italia e in più di 60 paesi dei diversi continenti. E&#39; &quot;Associazione pubblica di laici della Chiesa&quot;. Le differenti comunità, sparse nel mondo, condividono la stessa spiritualità e i fondamenti che caratterizzano il cammino di Sant’Egidio.', 'http://www.santegidio.org/it/index.html', 26, 1, 412, 0, 'emanuel.righetto@tin.it', 1, 0, '2003-12-30 21:24:59', '', '', ''),
(31, '35MM', 'Portale italiano dedicato ai Film, cinema, Tv, Home Video.\nRecensione e trama di tutti i filmi usciti e in uscita.', 'http://www.35mm.it/', 18, 1, 233, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-01-04 18:29:40', '', '', ''),
(32, 'CITAZIONI E FRASI FAMOSE DA FILM', 'Questa sezione del sito umoristico del Dr Zap raccoglie centinaia di battute famose, tratte da film, in maggioranza di tipo umoristico. Sono catalogate in ordine alfabetico in base al titolo del film.', 'http://www.drzap.it/film_citazioni.htm', 18, 1, 452, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-01-22 20:49:04', '', '', ''),
(33, 'Testi Mania', 'Testi Musicali Testi di Canzoni Song Lyrics: il più grande database di testi musicali.', 'http://www.testimania.com/', 19, 1, 1469, 0, 'emanuel@righetto@tin.it', 1, 0, '2004-03-27 13:02:05', '', '', ''),
(34, 'La pagina  degli ex-allievi del seminario francescano di San Daniele in Lonigo', 'Questa è la pagina  degli ex-allievi del seminario francescano di San Daniele in Lonigo e dei loro amici e simpatizzanti', 'http://exallievi.interfree.it', 31, 1, 212, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-04-01 13:55:24', '', '', ''),
(36, 'FESTA GRANDE SANTA VIOLA', 'Festa all&#39;aperto, chioschi enogastronomici, intrattenimenti danzanti vari, discoteca, ballo liscio, birra a fiumi ... Azzago, Grezzana, Verona.', 'http://www.santaviola.com/', 13, 1, 898, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-08-10 14:09:54', '', '', ''),
(37, 'GARE NAZIONALI DI MOUNTAIN BIKE', 'Tutti i link alle gare MTB più importanti', 'http://www.skyvolley.net/public/files/Link_granfondo-ita.html', 14, 1, 420, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-09-08 13:51:02', '', '', ''),
(38, 'A.S. Basalti - San Giovanni Ilarione', 'Sito dell&#39;associazione sportiva Basalti di San Giovanni Ilarione (Verona) affiliata UDACE.', 'http://www.asbasalti.it', 14, 302, 3924, 7, 'info@asbasalti.it', 1, 0, '2004-10-06 14:41:57', '', '', ''),
(40, 'Federazione Internazionale Volley Ball', 'FIVB Official volleyball rules: il sito ufficiale della pallavolo con il regolamento sempre aggiornato. Solo in Inglese o Francese.', 'http://www.fivb.ch/', 6, 1, 119, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-10-09 13:20:02', '', '', ''),
(41, 'Federazione Italiana Volley', 'Sei un tifoso o vuoi imparare a conoscere il mondo del volley? Qui trovi documenti, comunicati, risultati, regolamenti, norme, contributi, modulistica.  ', 'http://www.federvolley.it', 6, 1, 123, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-10-09 13:34:42', '', '', ''),
(42, 'Gruppo Gastrofili Val d&#39;Alpone Verona', 'L&#39;unico gruppo &quot;ufficiale&quot; d&#39;Italia che unisce la passione per l&#39;astronomia con il piacere e la cultura della buona tavola...!', 'http://www.gastrofili.it', 50, 3, 5478, 0, 'gastrofili@tiscalinet.it', 1, 0, '2004-10-09 16:47:25', '', '', ''),
(50, 'attivissimo.net', 'Paolo Attivissimo - antibufala, Internet per tutti, Da Windows a Linux e altri deliri', 'http://www.attivissimo.net', 9, 1, 305, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-11-27 19:39:45', '', '', ''),
(51, 'Guida al posizionamento dei siti web nei motori di ricerca', 'Cos&#39;è il &quot;posizionamento&quot;?\r\nPer posizionamento si intente un insieme di tecniche che hanno l&#39;obiettivo di migliorare la posizione di un sito web nei risultati delle ricerche nei motori di ricerca. ', 'http://www.motoricerca.info', 9, 1, 603, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-11-27 19:41:29', '', '', ''),
(52, 'Motori di ricerca: posizionamento, registrazione ed iscrizione gratuita su 200+ motori italiani e internazionali', 'Motori di ricerca: posizionamento, registrazione ed iscrizione gratuita su 200 motori di ricerca. Promozione ed ottimizzazione pagine internet per i motori di ricerca e le directory italiani ed internazionali. Iscrivi il tuo sito gratis!', 'http://www.submission.it', 9, 1, 385, 0, 'emanuel.righetto@tin.it', 1, 0, '2004-11-27 19:43:14', '', '', ''),
(53, 'Pink Volley', 'La società Pink Volley opera nel settore della pallavolo femminile dal 1992 e da sempre svolge attività sportiva come forma di educazione. Gioco e divertimento per i più piccoli, apprendimento e sviluppo delle tecniche per le atlete più grandi sono anche quest&#39;anno fili conduttori della nostra filosofia. È bene ricordare che il risultato sportivo non deve essere il primo obiettivo ma il traguardo raggiunto con la passione e la fatica di anni di allenamento. Nella passata stagione ‘03-&#39;04, il Pink Volley può pregiarsi di due promozioni di categoria: le ragazze più grandi, che giocano al Palazzetto dello Sport di Montecchia, hanno concluso il girone al primo posto, conquistando la promozione in Prima Divisione. La squadra di Vestenanova, che gioca nella Palestra di San Giovanni, ha raggiunto il passaggio in Seconda Divisione dominando il proprio girone: 15 incontri vinti sui 16 disputati.', 'http://www.pinkvolley.it', 8, 12, 842, 0, 'emanuel.righetto@tin.it', 1, 0, '2005-01-26 12:00:15', '', '', ''),
(55, 'Flogisto2 - Progettazione e installazione caminetti, stufe, recupero camini esistenti.', 'Progettazione su misura di camini aperti e termo caminetti. Vasta gamma di inserti con possibilità di canalizzare l’aria calda. Recupero caminetti esistenti.', 'http://www.flogisto2.it', 64, 0, 0, 0, 'emanuel.righetto@tin.it', 2, 0, '2006-03-25 20:11:51', '', '', ''),
(57, 'PASSIONE VOLLEY', 'Sito non ufficiale del GS Volley Olmi di Milano - dedicato agli amanti della pallavolo', 'http://www.passionevolley.it', 6, 0, 1444, 0, 'panara.gianluca@alice.it', 1, 0, '2006-05-30 19:31:27', '', '', ''),
(64, 'Negretto Camini d''Autore', 'Giuseppe Negretto, dal 1995 mette a frutto la sua esperienza professionale, nella costruzione di camini artigianali con materiali di recupero e di caminetti dal design moderno o classico. Si avvale della preziosa collaborazione di prestigiosi studi di architettura, per fornire un servizio completo, dalla progettazione alla realizzazione di complementi di arredo per interni.', 'https://www.negrettocamini.it', 64, 0, 0, 0, 'emanuel.righetto@tin.it', 2, 0, '2007-06-15 01:49:14', '', '', ''),
(65, 'Cortona Volley', 'Portale Associazione Sportiva Dilettantisca Cortona Volley, pallavolo Serie B2 maschile, Serie D femminile, Settore giovanile maschile e femminile', 'http://www.cortonavolley.it', 6, 0, 738, 0, 'info@cortonavolley.it', 1, 0, '2007-10-09 16:14:27', '', '', ''),
(66, 'Studio Associato di Topografia', 'S.a.t. opera dal 1997 nel settore della topografia e dell''edilizia a supporto di privati, enti pubblici, imprese e liberi professionisti.', 'http://www.satgeorilievi.com', 67, 0, 0, 0, 'emanuel.righetto@tin.it', 2, 0, '2007-10-22 09:55:07', '', '', ''),
(67, 'Pensilina Pe.TraPark', 'La ditta PE.TRA srl, sfruttando le conoscenze nel settore propone le migliori soluzioni alle esigenze di copertura e di chiusura, sia per il settore industriale sia per il settore residenziale.\r\n\r\nAvvalendosi di collaboratori altamente qualificati si pone come obiettivo quello di consigliare e seguire i propri clienti sin dai primi contatti nella ricerca di soluzioni idonee a soddisfare qualsiasi esigenza e garantendo l''assistenza ai propri prodotti anche dopo la vendita.', 'http://www.petrapark.it', 64, 0, 0, 0, 'emanuel.righetto@tin.it', 2, 0, '2007-12-19 09:29:41', '', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
