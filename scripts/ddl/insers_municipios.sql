
-- Insertar pais Colombia
INSERT INTO tab_paises (id_pais, cod_iso, nom_pais, arancel_pct)
VALUES (1, 170, 'Colombia', 0.00);
-- Insertar los 33 departamentos de Colombia
INSERT INTO tab_departamentos (id_pais, id_departamento, nom_departamento) VALUES
(1, 1, 'Amazonas'),(1, 2, 'Antioquia'),(1, 3, 'Arauca'),(1, 4, 'Atlantico'),
(1, 5, 'Bolivar'),(1, 6, 'Boyaca'),(1, 7, 'Caldas'),(1, 8, 'Caqueta'),
(1, 9, 'Casanare'),(1, 10, 'Cauca'),(1, 11, 'Cesar'),(1, 12, 'Choco'),
(1, 13, 'Cordoba'),(1, 14, 'Cundinamarca'),(1, 15, 'Guainia'),(1, 16, 'Guaviare'),
(1, 17, 'Huila'),(1, 18, 'La Guajira'),(1, 19, 'Magdalena'),(1, 20, 'Meta'),
(1, 21, 'Narino'),(1, 22, 'Norte de Santander'),(1, 23, 'Putumayo'),(1, 24, 'Quindio'),
(1, 25, 'Risaralda'),(1, 26, 'San Andres y Providencia'),(1, 27, 'Santander'),(1, 28, 'Sucre'),
(1, 29, 'Tolima'),(1, 30, 'Valle del Cauca'),(1, 31, 'Vaupes'),(1, 32, 'Vichada'),
(1, 33, 'Bogota D.C.');

-- AMAZONAS - 2 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,1,1,'Leticia','910001'),(1,1,2,'Puerto Narino','910540');

-- ANTIOQUIA - 125 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,2,1,'Medellin','050001'),(1,2,2,'Abejorral','050002'),(1,2,3,'Abriaqui','050004'),
(1,2,4,'Alejandria','050021'),(1,2,5,'Amaga','050030'),(1,2,6,'Amalfi','050031'),
(1,2,7,'Andes','050034'),(1,2,8,'Angelopolis','050036'),(1,2,9,'Angostura','050038'),
(1,2,10,'Anori','050040'),(1,2,11,'Santafe de Antioquia','050042'),(1,2,12,'Anza','050044'),
(1,2,13,'Apartado','050045'),(1,2,14,'Arboletes','050051'),(1,2,15,'Argelia','050055'),
(1,2,16,'Armenia','050059'),(1,2,17,'Barbosa','050079'),(1,2,18,'Belmira','050086'),
(1,2,19,'Bello','050088'),(1,2,20,'Betania','050091'),(1,2,21,'Betulia','050093'),
(1,2,22,'Ciudad Bolivar','050101'),(1,2,23,'Briceno','050107'),(1,2,24,'Buritica','050113'),
(1,2,25,'Caceres','050120'),(1,2,26,'Caicedo','050125'),(1,2,27,'Caldas','050129'),
(1,2,28,'Campamento','050134'),(1,2,29,'Canasgordas','050138'),(1,2,30,'Caracoli','050142'),
(1,2,31,'Caramanta','050145'),(1,2,32,'Carepa','050147'),(1,2,33,'El Carmen de Viboral','050148'),
(1,2,34,'Carolina','050150'),(1,2,35,'Caucasia','050154'),(1,2,36,'Chigorodo','050172'),
(1,2,37,'Cisneros','050190'),(1,2,38,'Cocorna','050197'),(1,2,39,'Concepcion','050206'),
(1,2,40,'Concordia','050209'),(1,2,41,'Copacabana','050212'),(1,2,42,'Dabeiba','050234'),
(1,2,43,'Donmatias','050237'),(1,2,44,'Ebejico','050240'),(1,2,45,'El Bagre','050250'),
(1,2,46,'Enterrios','050264'),(1,2,47,'Envigado','050266'),(1,2,48,'Fredonia','050282'),
(1,2,49,'Frontino','050284'),(1,2,50,'Giraldo','050306'),(1,2,51,'Girardota','050308'),
(1,2,52,'Gomez Plata','050310'),(1,2,53,'Granada','050313'),(1,2,54,'Guadalupe','050315'),
(1,2,55,'Guarne','050318'),(1,2,56,'Guatape','050321'),(1,2,57,'Heliconia','050347'),
(1,2,58,'Hispania','050353'),(1,2,59,'Itagui','050360'),(1,2,60,'Ituango','050361'),
(1,2,61,'Jardin','050364'),(1,2,62,'Jerico','050368'),(1,2,63,'La Ceja','050376'),
(1,2,64,'La Estrella','050380'),(1,2,65,'La Pintada','050390'),(1,2,66,'La Union','050400'),
(1,2,67,'Liborina','050411'),(1,2,68,'Maceo','050425'),(1,2,69,'Marinilla','050440'),
(1,2,70,'Montebello','050467'),(1,2,71,'Murindo','050475'),(1,2,72,'Mutata','050480'),
(1,2,73,'Narino','050483'),(1,2,74,'Necocli','050490'),(1,2,75,'Nechi','050495'),
(1,2,76,'Olaya','050501'),(1,2,77,'Penol','050541'),(1,2,78,'Peque','050543'),
(1,2,79,'Pueblorrico','050576'),(1,2,80,'Puerto Berrio','050579'),(1,2,81,'Puerto Nare','050585'),
(1,2,82,'Puerto Triunfo','050591'),(1,2,83,'Remedios','050604'),(1,2,84,'Retiro','050607'),
(1,2,85,'Rionegro','050615'),(1,2,86,'Sabanalarga','050628'),(1,2,87,'Sabaneta','050631'),
(1,2,88,'Salgar','050642'),(1,2,89,'San Andres de Cuerquia','050647'),(1,2,90,'San Carlos','050649'),
(1,2,91,'San Francisco','050652'),(1,2,92,'San Jeronimo','050656'),(1,2,93,'San Jose de la Montana','050658'),
(1,2,94,'San Juan de Uraba','050659'),(1,2,95,'San Luis','050660'),(1,2,96,'San Pedro de los Milagros','050664'),
(1,2,97,'San Pedro de Uraba','050665'),(1,2,98,'San Rafael','050667'),(1,2,99,'San Roque','050670'),
(1,2,100,'San Vicente Ferrer','050674'),(1,2,101,'Santa Barbara','050679'),(1,2,102,'Santa Rosa de Osos','050686'),
(1,2,103,'Santo Domingo','050690'),(1,2,104,'El Santuario','050697'),(1,2,105,'Segovia','050736'),
(1,2,106,'Sonson','050756'),(1,2,107,'Sopetran','050761'),(1,2,108,'Tamesis','050789'),
(1,2,109,'Taraza','050790'),(1,2,110,'Tarso','050792'),(1,2,111,'Titiribi','050809'),
(1,2,112,'Toledo','050819'),(1,2,113,'Turbo','050837'),(1,2,114,'Uramita','050842'),
(1,2,115,'Urrao','050847'),(1,2,116,'Valdivia','050853'),(1,2,117,'Valparaiso','050854'),
(1,2,118,'Vegachi','050856'),(1,2,119,'Venecia','050858'),(1,2,120,'Vigia del Fuerte','050861'),
(1,2,121,'Yali','050873'),(1,2,122,'Yarumal','050875'),(1,2,123,'Yolombo','050877'),
(1,2,124,'Yondo','050879'),(1,2,125,'Zaragoza','050885');

-- ARAUCA - 7 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,3,1,'Arauca','810001'),(1,3,2,'Arauquita','810050'),(1,3,3,'Cravo Norte','810240'),
(1,3,4,'Fortul','810250'),(1,3,5,'Puerto Rondon','810591'),(1,3,6,'Saravena','810670'),
(1,3,7,'Tame','810794');

-- ATLANTICO - 23 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,4,1,'Barranquilla','080001'),(1,4,2,'Baranoa','080078'),(1,4,3,'Campo de la Cruz','080137'),
(1,4,4,'Candelaria','080141'),(1,4,5,'Galapa','080296'),(1,4,6,'Juan de Acosta','080372'),
(1,4,7,'Luruaco','080421'),(1,4,8,'Malambo','080433'),(1,4,9,'Manati','080436'),
(1,4,10,'Palmar de Varela','080520'),(1,4,11,'Piojo','080549'),(1,4,12,'Polonuevo','080558'),
(1,4,13,'Ponedera','080560'),(1,4,14,'Puerto Colombia','080573'),(1,4,15,'Repelon','080606'),
(1,4,16,'Sabanagrande','080620'),(1,4,17,'Sabanalarga','080621'),(1,4,18,'Santa Lucia','080675'),
(1,4,19,'Santo Tomas','080685'),(1,4,20,'Soledad','080758'),(1,4,21,'Suan','080770'),
(1,4,22,'Tubara','080832'),(1,4,23,'Usiacuri','080849');

-- BOLIVAR - 46 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,5,1,'Cartagena','130001'),(1,5,2,'Achi','130006'),(1,5,3,'Altos del Rosario','130030'),
(1,5,4,'Arenal','130042'),(1,5,5,'Arjona','130052'),(1,5,6,'Arroyohondo','130062'),
(1,5,7,'Barranco de Loba','130074'),(1,5,8,'Calamar','130140'),(1,5,9,'Cantagallo','130160'),
(1,5,10,'Cicuco','130188'),(1,5,11,'Cordoba','130212'),(1,5,12,'Clemencia','130215'),
(1,5,13,'El Carmen de Bolivar','130244'),(1,5,14,'El Guamo','130248'),(1,5,15,'El Penon','130268'),
(1,5,16,'Hatillo de Loba','130300'),(1,5,17,'Magangue','130428'),(1,5,18,'Mahates','130433'),
(1,5,19,'Margarita','130440'),(1,5,20,'Maria la Baja','130442'),(1,5,21,'Montecristo','130458'),
(1,5,22,'Mompos','130468'),(1,5,23,'Morales','130473'),(1,5,24,'Norosi','130490'),
(1,5,25,'Pinillos','130517'),(1,5,26,'Regidor','130580'),(1,5,27,'Rio Viejo','130600'),
(1,5,28,'San Cristobal','130620'),(1,5,29,'San Estanislao','130647'),(1,5,30,'San Fernando','130650'),
(1,5,31,'San Jacinto','130654'),(1,5,32,'San Jacinto del Cauca','130655'),(1,5,33,'San Juan Nepomuceno','130657'),
(1,5,34,'San Martin de Loba','130660'),(1,5,35,'San Pablo','130670'),(1,5,36,'Santa Catalina','130673'),
(1,5,37,'Santa Rosa','130683'),(1,5,38,'Santa Rosa del Sur','130688'),(1,5,39,'Simiti','130744'),
(1,5,40,'Soplaviento','130760'),(1,5,41,'Talaigua Nuevo','130780'),(1,5,42,'Tiquisio','130810'),
(1,5,43,'Turbaco','130823'),(1,5,44,'Turbana','130827'),(1,5,45,'Villanueva','130870'),
(1,5,46,'Zambrano','130890');

-- BOYACA - 123 municipios (primeros 50)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,6,1,'Tunja','150001'),(1,6,2,'Almeida','150047'),(1,6,3,'Aquitania','150060'),
(1,6,4,'Arcabuco','150067'),(1,6,5,'Belen','150090'),(1,6,6,'Berbeo','150094'),
(1,6,7,'Beteitiva','150104'),(1,6,8,'Boavita','150110'),(1,6,9,'Boyaca','150120'),
(1,6,10,'Briceno','150130'),(1,6,11,'Buenavista','150140'),(1,6,12,'Busbanza','150150'),
(1,6,13,'Caldas','150160'),(1,6,14,'Campohermoso','150170'),(1,6,15,'Cerinza','150180'),
(1,6,16,'Chinavita','150190'),(1,6,17,'Chiquinquira','150200'),(1,6,18,'Chiscas','150210'),
(1,6,19,'Chita','150220'),(1,6,20,'Chitaraque','150230'),(1,6,21,'Chivata','150240'),
(1,6,22,'Cienega','150250'),(1,6,23,'Combita','150260'),(1,6,24,'Coper','150270'),
(1,6,25,'Corrales','150280'),(1,6,26,'Covarachia','150290'),(1,6,27,'Cubara','150300'),
(1,6,28,'Cucaita','150310'),(1,6,29,'Cuitiva','150320'),(1,6,30,'Chiquiza','150330'),
(1,6,31,'Chivor','150340'),(1,6,32,'Duitama','150380'),(1,6,33,'El Cocuy','150400'),
(1,6,34,'El Espino','150410'),(1,6,35,'Firavitoba','150440'),(1,6,36,'Floresta','150450'),
(1,6,37,'Gachantiva','150460'),(1,6,38,'Gameza','150470'),(1,6,39,'Garagoa','150480'),
(1,6,40,'Guacamayas','150490'),(1,6,41,'Guateque','150500'),(1,6,42,'Guayata','150510'),
(1,6,43,'Guican','150520'),(1,6,44,'Iza','150530'),(1,6,45,'Jenesano','150540'),
(1,6,46,'Jerico','150550'),(1,6,47,'Labranzagrande','150560'),(1,6,48,'La Capilla','150570'),
(1,6,49,'La Victoria','150580'),(1,6,50,'La Uvita','150590');

-- BOYACA continuacion (51-100)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,6,51,'Villa de Leyva','150600'),(1,6,52,'Macanal','150610'),(1,6,53,'Maripi','150620'),
(1,6,54,'Miraflores','150630'),(1,6,55,'Mongua','150640'),(1,6,56,'Mongui','150650'),
(1,6,57,'Moniquira','150660'),(1,6,58,'Motavita','150670'),(1,6,59,'Muzo','150680'),
(1,6,60,'Nobsa','150690'),(1,6,61,'Nuevo Colon','150700'),(1,6,62,'Oicata','150710'),
(1,6,63,'Otanche','150720'),(1,6,64,'Pachavita','150730'),(1,6,65,'Paez','150740'),
(1,6,66,'Paipa','150750'),(1,6,67,'Pajarito','150760'),(1,6,68,'Panqueba','150770'),
(1,6,69,'Pauna','150780'),(1,6,70,'Paya','150790'),(1,6,71,'Paz de Rio','150800'),
(1,6,72,'Pesca','150810'),(1,6,73,'Pisba','150820'),(1,6,74,'Puerto Boyaca','150860'),
(1,6,75,'Quipama','150870'),(1,6,76,'Ramiriqui','150880'),(1,6,77,'Raquira','150890'),
(1,6,78,'Rondon','150900'),(1,6,79,'Saboya','150910'),(1,6,80,'Sachica','150920'),
(1,6,81,'Samaca','150930'),(1,6,82,'San Eduardo','150940'),(1,6,83,'San Jose de Pare','150950'),
(1,6,84,'San Luis de Gaceno','150960'),(1,6,85,'San Mateo','150970'),(1,6,86,'San Miguel de Sema','150980'),
(1,6,87,'San Pablo de Borbur','150990'),(1,6,88,'Santana','151000'),(1,6,89,'Santa Maria','151010'),
(1,6,90,'Santa Rosa de Viterbo','151020'),(1,6,91,'Santa Sofia','151030'),(1,6,92,'Sativanorte','151040'),
(1,6,93,'Sativasur','151050'),(1,6,94,'Siachoque','151060'),(1,6,95,'Soata','151070'),
(1,6,96,'Socota','151080'),(1,6,97,'Socha','151090'),(1,6,98,'Sogamoso','151100'),
(1,6,99,'Somondoco','151110'),(1,6,100,'Sora','151120');

-- BOYACA continuacion (101-123)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,6,101,'Sotaquira','151130'),(1,6,102,'Soraca','151140'),(1,6,103,'Susacon','151150'),
(1,6,104,'Sutamarchan','151160'),(1,6,105,'Sutatenza','151170'),(1,6,106,'Tasco','151180'),
(1,6,107,'Tenza','151190'),(1,6,108,'Tibana','151200'),(1,6,109,'Tibasosa','151210'),
(1,6,110,'Tinjaca','151220'),(1,6,111,'Tipacoque','151230'),(1,6,112,'Toca','151240'),
(1,6,113,'Togui','151250'),(1,6,114,'Topaga','151260'),(1,6,115,'Tota','151270'),
(1,6,116,'Tunungu a','151280'),(1,6,117,'Turmeque','151290'),(1,6,118,'Tuta','151300'),
(1,6,119,'Tutaza','151310'),(1,6,120,'Umbita','151320'),(1,6,121,'Ventaquemada','151330'),
(1,6,122,'Viracach a','151340'),(1,6,123,'Zetaquira','151360');
-- CALDAS - 27 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,7,1,'Manizales','170001'),(1,7,2,'Aguadas','170013'),(1,7,3,'Anserma','170040'),
(1,7,4,'Aranzazu','170055'),(1,7,5,'Belalcazar','170088'),(1,7,6,'Chinchina','170174'),
(1,7,7,'Filadelfia','170272'),(1,7,8,'La Dorada','170380'),(1,7,9,'La Merced','170388'),
(1,7,10,'Manzanares','170433'),(1,7,11,'Marmato','170442'),(1,7,12,'Marquetalia','170444'),
(1,7,13,'Marulanda','170446'),(1,7,14,'Neira','170489'),(1,7,15,'Norcasia','170495'),
(1,7,16,'Pacora','170513'),(1,7,17,'Palestina','170521'),(1,7,18,'Pensilvania','170541'),
(1,7,19,'Riosucio','170614'),(1,7,20,'Risaralda','170616'),(1,7,21,'Salamina','170653'),
(1,7,22,'Samana','170659'),(1,7,23,'San Jose','170665'),(1,7,24,'Supia','170777'),
(1,7,25,'Victoria','170867'),(1,7,26,'Villamaria','170873'),(1,7,27,'Viterbo','170877');

-- CAQUETA - 16 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,8,1,'Florencia','180001'),(1,8,2,'Albania','180029'),(1,8,3,'Belen de los Andaquies','180094'),
(1,8,4,'Cartagena del Chaira','180150'),(1,8,5,'Curillo','180205'),(1,8,6,'El Doncello','180247'),
(1,8,7,'El Paujil','180256'),(1,8,8,'La Montanita','180410'),(1,8,9,'Milan','180460'),
(1,8,10,'Morelia','180479'),(1,8,11,'Puerto Rico','180592'),(1,8,12,'San Jose del Fragua','180653'),
(1,8,13,'San Vicente del Caguan','180689'),(1,8,14,'Solano','180749'),(1,8,15,'Solita','180753'),
(1,8,16,'Valparaiso','180860');

-- CASANARE - 19 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,9,1,'Yopal','850001'),(1,9,2,'Aguazul','850015'),(1,9,3,'Chameza','850125'),
(1,9,4,'Hato Corozal','850300'),(1,9,5,'La Salina','850400'),(1,9,6,'Mani','850430'),
(1,9,7,'Monterrey','850469'),(1,9,8,'Nunchia','850500'),(1,9,9,'Orocue','850520'),
(1,9,10,'Paz de Ariporo','850568'),(1,9,11,'Pore','850590'),(1,9,12,'Recetor','850615'),
(1,9,13,'Sabanalarga','850650'),(1,9,14,'Sacama','850680'),(1,9,15,'San Luis de Palenque','850690'),
(1,9,16,'Tamara','850770'),(1,9,17,'Tauramena','850785'),(1,9,18,'Trinidad','850825'),
(1,9,19,'Villanueva','850875');

-- CAUCA - 42 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,10,1,'Popayan','190001'),(1,10,2,'Almaguer','190022'),(1,10,3,'Argelia','190050'),
(1,10,4,'Balboa','190075'),(1,10,5,'Bolivar','190110'),(1,10,6,'Buenos Aires','190130'),
(1,10,7,'Cajibio','190137'),(1,10,8,'Caldono','190142'),(1,10,9,'Caloto','190150'),
(1,10,10,'Corinto','190212'),(1,10,11,'El Tambo','190256'),(1,10,12,'Florencia','190290'),
(1,10,13,'Guachene','190318'),(1,10,14,'Guapi','190364'),(1,10,15,'Inza','190392'),
(1,10,16,'Jambalo','190397'),(1,10,17,'La Sierra','190418'),(1,10,18,'La Vega','190455'),
(1,10,19,'Lopez de Micay','190473'),(1,10,20,'Mercaderes','190513'),(1,10,21,'Miranda','190517'),
(1,10,22,'Morales','190532'),(1,10,23,'Padilla','190533'),(1,10,24,'Paez','190548'),
(1,10,25,'Patia','190573'),(1,10,26,'Piamonte','190585'),(1,10,27,'Piendamo','190622'),
(1,10,28,'Puerto Tejada','190659'),(1,10,29,'Purace','190693'),(1,10,30,'Rosas','190701'),
(1,10,31,'San Sebastian','190743'),(1,10,32,'Santander de Quilichao','190760'),(1,10,33,'Santa Rosa','190780'),
(1,10,34,'Silvia','190821'),(1,10,35,'Sotara','190824'),(1,10,36,'Suarez','190845'),
(1,10,37,'Sucre','190855'),(1,10,38,'Timbio','190865'),(1,10,39,'Timbiqui','190890'),
(1,10,40,'Toribio','190893'),(1,10,41,'Totoro','190895'),(1,10,42,'Villa Rica','190897');

-- CESAR - 25 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,11,1,'Valledupar','200001'),(1,11,2,'Aguachica','200011'),(1,11,3,'Agustin Codazzi','200013'),
(1,11,4,'Astrea','200045'),(1,11,5,'Becerril','200060'),(1,11,6,'Bosconia','200110'),
(1,11,7,'Chimichagua','200175'),(1,11,8,'Chiriguana','200178'),(1,11,9,'Curumani','200228'),
(1,11,10,'El Copey','200238'),(1,11,11,'El Paso','200250'),(1,11,12,'Gamarra','200295'),
(1,11,13,'Gonzalez','200310'),(1,11,14,'La Gloria','200383'),(1,11,15,'La Jagua de Ibirico','200400'),
(1,11,16,'Manaure Balcon del Cesar','200443'),(1,11,17,'Pailitas','200517'),(1,11,18,'Pelaya','200550'),
(1,11,19,'Pueblo Bello','200570'),(1,11,20,'Rio de Oro','200614'),(1,11,21,'La Paz','200621'),
(1,11,22,'San Alberto','200647'),(1,11,23,'San Diego','200670'),(1,11,24,'San Martin','200680'),
(1,11,25,'Tamalameque','200770');

-- CHOCO - 30 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,12,1,'Quibdo','270001'),(1,12,2,'Acandi','270006'),(1,12,3,'Alto Baudo','270025'),
(1,12,4,'Atrato','270050'),(1,12,5,'Bagado','270073'),(1,12,6,'Bahia Solano','270075'),
(1,12,7,'Bajo Baudo','270077'),(1,12,8,'Bojaya','270099'),(1,12,9,'Canton de San Pablo','270135'),
(1,12,10,'Certegui','270150'),(1,12,11,'Condoto','270205'),(1,12,12,'El Carmen de Atrato','270245'),
(1,12,13,'El Litoral del San Juan','270250'),(1,12,14,'Istmina','270361'),(1,12,15,'Jurado','270372'),
(1,12,16,'Lloro','270413'),(1,12,17,'Medio Atrato','270425'),(1,12,18,'Medio Baudo','270430'),
(1,12,19,'Medio San Juan','270450'),(1,12,20,'Novita','270491'),(1,12,21,'Nuqui','270495'),
(1,12,22,'Rio Iro','270580'),(1,12,23,'Rio Quito','270600'),(1,12,24,'Riosucio','270615'),
(1,12,25,'San Jose del Palmar','270660'),(1,12,26,'Sipi','270745'),(1,12,27,'Tado','270787'),
(1,12,28,'Unguia','270800'),(1,12,29,'Union Panamericana','270810'),(1,12,30,'Carmen del Darien','270820');

-- CORDOBA - 30 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,13,1,'Monteria','230001'),(1,13,2,'Ayapel','230052'),(1,13,3,'Buenavista','230090'),
(1,13,4,'Canalete','230095'),(1,13,5,'Cerete','230162'),(1,13,6,'Chima','230168'),
(1,13,7,'Chinu','230182'),(1,13,8,'Cienaga de Oro','230189'),(1,13,9,'Cotorra','230223'),
(1,13,10,'La Apartada','230350'),(1,13,11,'Lorica','230417'),(1,13,12,'Los Cordobas','230419'),
(1,13,13,'Momil','230464'),(1,13,14,'Montelibano','230466'),(1,13,15,'Monitos','230500'),
(1,13,16,'Planeta Rica','230555'),(1,13,17,'Pueblo Nuevo','230570'),(1,13,18,'Puerto Escondido','230580'),
(1,13,19,'Puerto Libertador','230586'),(1,13,20,'Purisima','230600'),(1,13,21,'Sahagun','230650'),
(1,13,22,'San Andres de Sotavento','230660'),(1,13,23,'San Antero','230670'),(1,13,24,'San Bernardo del Viento','230672'),
(1,13,25,'San Carlos','230675'),(1,13,26,'San Jose de Ure','230678'),(1,13,27,'San Pelayo','230682'),
(1,13,28,'Tierralta','230807'),(1,13,29,'Tuchin','230855'),(1,13,30,'Valencia','230867');
-- CUNDINAMARCA - 116 municipios (parte 1: 1-58)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,14,1,'Agua de Dios','252001'),(1,14,2,'Alban','252019'),(1,14,3,'Anapoima','252035'),
(1,14,4,'Anolaima','252040'),(1,14,5,'Apulo','252053'),(1,14,6,'Arbelaez','252058'),
(1,14,7,'Beltran','252086'),(1,14,8,'Bituima','252095'),(1,14,9,'Bojaca','252099'),
(1,14,10,'Cabrera','252120'),(1,14,11,'Cachipay','252123'),(1,14,12,'Cajica','252126'),
(1,14,13,'Caparrapi','252148'),(1,14,14,'Caqueza','252151'),(1,14,15,'Carmen de Carupa','252154'),
(1,14,16,'Chaguani','252168'),(1,14,17,'Chia','252175'),(1,14,18,'Chipaque','252178'),
(1,14,19,'Choachi','252181'),(1,14,20,'Choconta','252183'),(1,14,21,'Cogua','252200'),
(1,14,22,'Cota','252214'),(1,14,23,'Cucunuba','252224'),(1,14,24,'El Colegio','252245'),
(1,14,25,'El Penon','252258'),(1,14,26,'El Rosal','252260'),(1,14,27,'Facatativa','252269'),
(1,14,28,'Fomeque','252279'),(1,14,29,'Fosca','252281'),(1,14,30,'Funza','252286'),
(1,14,31,'Fuquene','252288'),(1,14,32,'Fusagasuga','252290'),(1,14,33,'Gachala','252293'),
(1,14,34,'Gachancipa','252295'),(1,14,35,'Gacheta','252297'),(1,14,36,'Gama','252299'),
(1,14,37,'Girardot','252307'),(1,14,38,'Granada','252312'),(1,14,39,'Guacheta','252317'),
(1,14,40,'Guaduas','252320'),(1,14,41,'Guasca','252322'),(1,14,42,'Guataqui','252324'),
(1,14,43,'Guatavita','252326'),(1,14,44,'Guayabal de Siquima','252328'),(1,14,45,'Guayabetal','252335'),
(1,14,46,'Gutierrez','252339'),(1,14,47,'Jerusalen','252368'),(1,14,48,'Junin','252372'),
(1,14,49,'La Calera','252377'),(1,14,50,'La Mesa','252386'),(1,14,51,'La Palma','252394'),
(1,14,52,'La Pena','252398'),(1,14,53,'La Vega','252402'),(1,14,54,'Lenguazaque','252407'),
(1,14,55,'Macheta','252426'),(1,14,56,'Madrid','252430'),(1,14,57,'Manta','252436'),
(1,14,58,'Medina','252438');

-- CUNDINAMARCA continuacion (59-116)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,14,59,'Mosquera','252473'),(1,14,60,'Narino','252483'),(1,14,61,'Nemocon','252486'),
(1,14,62,'Nilo','252488'),(1,14,63,'Nimaima','252489'),(1,14,64,'Nocaima','252491'),
(1,14,65,'Venecia','252513'),(1,14,66,'Pacho','252518'),(1,14,67,'Paime','252524'),
(1,14,68,'Pandi','252530'),(1,14,69,'Paratebueno','252535'),(1,14,70,'Pasca','252539'),
(1,14,71,'Puerto Salgar','252572'),(1,14,72,'Puli','252580'),(1,14,73,'Quebradanegra','252592'),
(1,14,74,'Quetame','252594'),(1,14,75,'Quipile','252596'),(1,14,76,'Ricaurte','252612'),
(1,14,77,'San Antonio del Tequendama','252645'),(1,14,78,'San Bernardo','252649'),(1,14,79,'San Cayetano','252653'),
(1,14,80,'San Francisco','252658'),(1,14,81,'San Juan de Rioseco','252662'),(1,14,82,'Sasaima','252718'),
(1,14,83,'Sesquile','252736'),(1,14,84,'Sibate','252740'),(1,14,85,'Silvania','252743'),
(1,14,86,'Simijaca','252745'),(1,14,87,'Soacha','252754'),(1,14,88,'Sopo','252758'),
(1,14,89,'Subachoque','252769'),(1,14,90,'Suesca','252772'),(1,14,91,'Supata','252777'),
(1,14,92,'Susa','252779'),(1,14,93,'Sutatausa','252781'),(1,14,94,'Tabio','252785'),
(1,14,95,'Tausa','252793'),(1,14,96,'Tena','252797'),(1,14,97,'Tenjo','252799'),
(1,14,98,'Tibacuy','252805'),(1,14,99,'Tibirita','252807'),(1,14,100,'Tocaima','252815'),
(1,14,101,'Tocancipa','252817'),(1,14,102,'Topaipi','252823'),(1,14,103,'Ubala','252841'),
(1,14,104,'Ubaque','252843'),(1,14,105,'Une','252845'),(1,14,106,'Utica','252851'),
(1,14,107,'Vergara','252862'),(1,14,108,'Viani','252867'),(1,14,109,'Villagomez','252871'),
(1,14,110,'Villapinzon','252873'),(1,14,111,'Villeta','252875'),(1,14,112,'Viota','252878'),
(1,14,113,'Yacopi','252885'),(1,14,114,'Zipacon','252898'),(1,14,115,'Zipaquira','252899'),
(1,14,116,'Villa de San Diego de Ubate','252900');

-- GUAINIA - 1 municipio
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,15,1,'Inirida','940001');

-- GUAVIARE - 4 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,16,1,'San Jose del Guaviare','950001'),(1,16,2,'Calamar','950015'),
(1,16,3,'El Retorno','950025'),(1,16,4,'Miraflores','950035');

-- HUILA - 37 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,17,1,'Neiva','410001'),(1,17,2,'Acevedo','410013'),(1,17,3,'Agrado','410016'),
(1,17,4,'Aipe','410020'),(1,17,5,'Algeciras','410026'),(1,17,6,'Altamira','410030'),
(1,17,7,'Baraya','410078'),(1,17,8,'Campoalegre','410140'),(1,17,9,'Colombia','410205'),
(1,17,10,'Elias','410247'),(1,17,11,'Garzon','410298'),(1,17,12,'Gigante','410306'),
(1,17,13,'Guadalupe','410320'),(1,17,14,'Hobo','410340'),(1,17,15,'Iquira','410357'),
(1,17,16,'Isnos','410359'),(1,17,17,'La Argentina','410377'),(1,17,18,'La Plata','410378'),
(1,17,19,'Nataga','410473'),(1,17,20,'Oporapa','410503'),(1,17,21,'Paicol','410517'),
(1,17,22,'Palermo','410518'),(1,17,23,'Palestina','410530'),(1,17,24,'Pital','410551'),
(1,17,25,'Pitalito','410554'),(1,17,26,'Rivera','410606'),(1,17,27,'Saladoblanco','410660'),
(1,17,28,'San Agustin','410668'),(1,17,29,'Santa Maria','410676'),(1,17,30,'Suaza','410770'),
(1,17,31,'Tarqui','410791'),(1,17,32,'Tesalia','410799'),(1,17,33,'Tello','410801'),
(1,17,34,'Teruel','410807'),(1,17,35,'Timana','410855'),(1,17,36,'Villavieja','410885'),
(1,17,37,'Yaguara','410893');

-- LA GUAJIRA - 15 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,18,1,'Riohacha','440001'),(1,18,2,'Albania','440035'),(1,18,3,'Barrancas','440090'),
(1,18,4,'Dibulla','440098'),(1,18,5,'Distraccion','440110'),(1,18,6,'El Molino','440172'),
(1,18,7,'Fonseca','440245'),(1,18,8,'Hatonuevo','440250'),(1,18,9,'La Jagua del Pilar','440378'),
(1,18,10,'Maicao','440430'),(1,18,11,'Manaure','440560'),(1,18,12,'San Juan del Cesar','440650'),
(1,18,13,'Uribia','440847'),(1,18,14,'Urumita','440855'),(1,18,15,'Villanueva','440874');

-- MAGDALENA - 30 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,19,1,'Santa Marta','470001'),(1,19,2,'Algarrobo','470030'),(1,19,3,'Aracataca','470053'),
(1,19,4,'Ariguani','470058'),(1,19,5,'Cerro de San Antonio','470161'),(1,19,6,'Chivolo','470170'),
(1,19,7,'Cienaga','470175'),(1,19,8,'Concordia','470205'),(1,19,9,'El Banco','470244'),
(1,19,10,'El Pinon','470258'),(1,19,11,'El Reten','470260'),(1,19,12,'Fundacion','470268'),
(1,19,13,'Guamal','470306'),(1,19,14,'Nueva Granada','470460'),(1,19,15,'Pedraza','470541'),
(1,19,16,'Pijino del Carmen','470545'),(1,19,17,'Pivijay','470555'),(1,19,18,'Plato','470570'),
(1,19,19,'Puebloviejo','470605'),(1,19,20,'Remolino','470614'),(1,19,21,'Sabanas de San Angel','470645'),
(1,19,22,'Salamina','470660'),(1,19,23,'San Sebastian de Buenavista','470675'),(1,19,24,'San Zenon','470678'),
(1,19,25,'Santa Ana','470683'),(1,19,26,'Santa Barbara de Pinto','470686'),(1,19,27,'Sitionuevo','470745'),
(1,19,28,'Tenerife','470798'),(1,19,29,'Zapayan','470960'),(1,19,30,'Zona Bananera','470980');

-- META - 29 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,20,1,'Villavicencio','500001'),(1,20,2,'Acacias','500006'),(1,20,3,'Barranca de Upia','500110'),
(1,20,4,'Cabuyaro','500124'),(1,20,5,'Castilla la Nueva','500150'),(1,20,6,'Cubarral','500223'),
(1,20,7,'Cumaral','500226'),(1,20,8,'El Calvario','500245'),(1,20,9,'El Castillo','500251'),
(1,20,10,'El Dorado','500270'),(1,20,11,'Fuente de Oro','500287'),(1,20,12,'Granada','500313'),
(1,20,13,'Guamal','500318'),(1,20,14,'Mapiripan','500325'),(1,20,15,'Mesetas','500330'),
(1,20,16,'La Macarena','500350'),(1,20,17,'Uribe','500370'),(1,20,18,'Lejanias','500400'),
(1,20,19,'Puerto Concordia','500450'),(1,20,20,'Puerto Gaitan','500568'),(1,20,21,'Puerto Lopez','500573'),
(1,20,22,'Puerto Lleras','500577'),(1,20,23,'Puerto Rico','500590'),(1,20,24,'Restrepo','500606'),
(1,20,25,'San Carlos de Guaroa','500680'),(1,20,26,'San Juan de Arama','500683'),(1,20,27,'San Juanito','500686'),
(1,20,28,'San Martin','500689'),(1,20,29,'Vista Hermosa','500711');
-- NARINO - 64 municipios (parte 1: 1-32)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,21,1,'Pasto','520001'),(1,21,2,'Alban','520019'),(1,21,3,'Aldana','520022'),
(1,21,4,'Ancuya','520036'),(1,21,5,'Arboleda','520051'),(1,21,6,'Barbacoas','520075'),
(1,21,7,'Belen','520090'),(1,21,8,'Buesaco','520110'),(1,21,9,'Colon','520203'),
(1,21,10,'Consaca','520207'),(1,21,11,'Contadero','520210'),(1,21,12,'Cordoba','520215'),
(1,21,13,'Cuaspud','520224'),(1,21,14,'Cumbal','520227'),(1,21,15,'Cumbitara','520233'),
(1,21,16,'Chachagui','520240'),(1,21,17,'El Charco','520250'),(1,21,18,'El Penol','520254'),
(1,21,19,'El Rosario','520256'),(1,21,20,'El Tablon de Gomez','520258'),(1,21,21,'El Tambo','520260'),
(1,21,22,'Funes','520287'),(1,21,23,'Guachucal','520317'),(1,21,24,'Guaitarilla','520320'),
(1,21,25,'Gualmatan','520323'),(1,21,26,'Iles','520352'),(1,21,27,'Imues','520354'),
(1,21,28,'Ipiales','520356'),(1,21,29,'La Cruz','520378'),(1,21,30,'La Florida','520385'),
(1,21,31,'La Llanada','520390'),(1,21,32,'La Tola','520399');

-- NARINO continuacion (33-64)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,21,33,'La Union','520405'),(1,21,34,'Leiva','520411'),(1,21,35,'Linares','520418'),
(1,21,36,'Los Andes','520427'),(1,21,37,'Magui','520435'),(1,21,38,'Mallama','520436'),
(1,21,39,'Mosquera','520473'),(1,21,40,'Narino','520490'),(1,21,41,'Olaya Herrera','520506'),
(1,21,42,'Ospina','520520'),(1,21,43,'Francisco Pizarro','520540'),(1,21,44,'Policarpa','520560'),
(1,21,45,'Potosi','520565'),(1,21,46,'Providencia','520573'),(1,21,47,'Puerres','520585'),
(1,21,48,'Pupiales','520590'),(1,21,49,'Ricaurte','520612'),(1,21,50,'Roberto Payan','520621'),
(1,21,51,'Samaniego','520678'),(1,21,52,'Sandona','520683'),(1,21,53,'San Bernardo','520685'),
(1,21,54,'San Lorenzo','520687'),(1,21,55,'San Pablo','520693'),(1,21,56,'San Pedro de Cartago','520696'),
(1,21,57,'Santa Barbara','520699'),(1,21,58,'Santacruz','520720'),(1,21,59,'Sapuyes','520750'),
(1,21,60,'Taminango','520786'),(1,21,61,'Tangua','520788'),(1,21,62,'San Andres de Tumaco','520834'),
(1,21,63,'Tuquerres','520838'),(1,21,64,'Yacuanquer','520885');

-- NORTE DE SANTANDER - 40 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,22,1,'Cucuta','540001'),(1,22,2,'Abrego','540006'),(1,22,3,'Arboledas','540051'),
(1,22,4,'Bochalema','540099'),(1,22,5,'Bucarasica','540109'),(1,22,6,'Cacota','540125'),
(1,22,7,'Cachira','540128'),(1,22,8,'Chinacota','540172'),(1,22,9,'Chitaga','540174'),
(1,22,10,'Convencion','540206'),(1,22,11,'Cucutilla','540223'),(1,22,12,'Durania','540239'),
(1,22,13,'El Carmen','540245'),(1,22,14,'El Tarra','540250'),(1,22,15,'El Zulia','540262'),
(1,22,16,'Gramalote','540313'),(1,22,17,'Hacari','540318'),(1,22,18,'Herran','540347'),
(1,22,19,'Labateca','540385'),(1,22,20,'La Esperanza','540398'),(1,22,21,'La Playa','540405'),
(1,22,22,'Los Patios','540418'),(1,22,23,'Lourdes','540420'),(1,22,24,'Mutiscua','540460'),
(1,22,25,'Ocana','540498'),(1,22,26,'Pamplona','540518'),(1,22,27,'Pamplonita','540520'),
(1,22,28,'Puerto Santander','540553'),(1,22,29,'Ragonvalia','540599'),(1,22,30,'Salazar','540660'),
(1,22,31,'San Calixto','540670'),(1,22,32,'San Cayetano','540673'),(1,22,33,'Santiago','540680'),
(1,22,34,'Sardinata','540720'),(1,22,35,'Silos','540743'),(1,22,36,'Teorama','540800'),
(1,22,37,'Tibu','540807'),(1,22,38,'Toledo','540820'),(1,22,39,'Villa Caro','540871'),
(1,22,40,'Villa del Rosario','540874');

-- PUTUMAYO - 13 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,23,1,'Mocoa','860001'),(1,23,2,'Colon','860150'),(1,23,3,'Orito','860287'),
(1,23,4,'Puerto Asis','860320'),(1,23,5,'Puerto Caicedo','860350'),(1,23,6,'Puerto Guzman','860380'),
(1,23,7,'Leguizamo','860420'),(1,23,8,'Sibundoy','860560'),(1,23,9,'San Francisco','860620'),
(1,23,10,'San Miguel','860670'),(1,23,11,'Santiago','860730'),(1,23,12,'Valle del Guamuez','860750'),
(1,23,13,'Villagarzon','860760');

-- QUINDIO - 12 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,24,1,'Armenia','630001'),(1,24,2,'Buenavista','630111'),(1,24,3,'Calarca','630150'),
(1,24,4,'Circasia','630175'),(1,24,5,'Cordoba','630212'),(1,24,6,'Filandia','630272'),
(1,24,7,'Genova','630302'),(1,24,8,'La Tebaida','630407'),(1,24,9,'Montenegro','630470'),
(1,24,10,'Pijao','630548'),(1,24,11,'Quimbaya','630594'),(1,24,12,'Salento','630670');

-- RISARALDA - 14 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,25,1,'Pereira','660001'),(1,25,2,'Apia','660045'),(1,25,3,'Balboa','660075'),
(1,25,4,'Belen de Umbria','660088'),(1,25,5,'Dosquebradas','660175'),(1,25,6,'Guatica','660318'),
(1,25,7,'La Celia','660383'),(1,25,8,'La Virginia','660400'),(1,25,9,'Marsella','660440'),
(1,25,10,'Mistrato','660456'),(1,25,11,'Pueblo Rico','660572'),(1,25,12,'Quinchia','660594'),
(1,25,13,'Santa Rosa de Cabal','660687'),(1,25,14,'Santuario','660689');

-- SAN ANDRES Y PROVIDENCIA - 2 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,26,1,'San Andres','880001'),(1,26,2,'Providencia','880002');

-- SANTANDER - 87 municipios (parte 1: 1-44)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,27,1,'Bucaramanga','680001'),(1,27,2,'Aguada','680013'),(1,27,3,'Albania','680020'),
(1,27,4,'Aratoca','680051'),(1,27,5,'Barbosa','680081'),(1,27,6,'Barichara','680082'),
(1,27,7,'Barrancabermeja','680093'),(1,27,8,'Betulia','680101'),(1,27,9,'Bolivar','680106'),
(1,27,10,'Cabrera','680122'),(1,27,11,'California','680132'),(1,27,12,'Capitanejo','680147'),
(1,27,13,'Carcasi','680152'),(1,27,14,'Cepita','680160'),(1,27,15,'Cerrito','680162'),
(1,27,16,'Charala','680167'),(1,27,17,'Charta','680169'),(1,27,18,'Chima','680172'),
(1,27,19,'Chipata','680176'),(1,27,20,'Cimitarra','680190'),(1,27,21,'Concepcion','680203'),
(1,27,22,'Confines','680206'),(1,27,23,'Contratacion','680209'),(1,27,24,'Coromoro','680211'),
(1,27,25,'Curiti','680229'),(1,27,26,'El Carmen de Chucuri','680235'),(1,27,27,'El Guacamayo','680238'),
(1,27,28,'El Penon','680244'),(1,27,29,'El Playon','680245'),(1,27,30,'Encino','680250'),
(1,27,31,'Enciso','680251'),(1,27,32,'Florian','680264'),(1,27,33,'Floridablanca','680275'),
(1,27,34,'Galan','680279'),(1,27,35,'Gambita','680281'),(1,27,36,'Giron','680307'),
(1,27,37,'Guaca','680318'),(1,27,38,'Guadalupe','680320'),(1,27,39,'Guapota','680322'),
(1,27,40,'Guavata','680324'),(1,27,41,'Guepsa','680327'),(1,27,42,'Hato','680344'),
(1,27,43,'Jesus Maria','680368'),(1,27,44,'Jordan','680370');

-- SANTANDER continuacion (45-87)
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,27,45,'La Belleza','680377'),(1,27,46,'Landazuri','680383'),(1,27,47,'La Paz','680385'),
(1,27,48,'Lebrija','680406'),(1,27,49,'Los Santos','680418'),(1,27,50,'Macaravita','680425'),
(1,27,51,'Malaga','680432'),(1,27,52,'Matanza','680444'),(1,27,53,'Mogotes','680464'),
(1,27,54,'Molagavita','680468'),(1,27,55,'Ocamonte','680498'),(1,27,56,'Oiba','680500'),
(1,27,57,'Onzaga','680502'),(1,27,58,'Palmar','680522'),(1,27,59,'Palmas del Socorro','680524'),
(1,27,60,'Paramo','680533'),(1,27,61,'Piedecuesta','680547'),(1,27,62,'Pinchote','680549'),
(1,27,63,'Puente Nacional','680572'),(1,27,64,'Puerto Parra','680573'),(1,27,65,'Puerto Wilches','680576'),
(1,27,66,'Rionegro','680614'),(1,27,67,'Sabana de Torres','680638'),(1,27,68,'San Andres','680646'),
(1,27,69,'San Benito','680650'),(1,27,70,'San Gil','680676'),(1,27,71,'San Joaquin','680678'),
(1,27,72,'San Jose de Miranda','680682'),(1,27,73,'San Miguel','680684'),(1,27,74,'San Vicente de Chucuri','680686'),
(1,27,75,'Santa Barbara','680705'),(1,27,76,'Santa Helena del Opon','680720'),(1,27,77,'Simacota','680745'),
(1,27,78,'Socorro','680770'),(1,27,79,'Suaita','680773'),(1,27,80,'Sucre','680790'),
(1,27,81,'Surata','680804'),(1,27,82,'Tona','680820'),(1,27,83,'Valle de San Jose','680855'),
(1,27,84,'Velez','680861'),(1,27,85,'Vetas','680862'),(1,27,86,'Villanueva','680872'),
(1,27,87,'Zapatoca','680895');
-- SUCRE - 26 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,28,1,'Sincelejo','700001'),(1,28,2,'Buenavista','700110'),(1,28,3,'Caimito','700124'),
(1,28,4,'Coloso','700204'),(1,28,5,'Corozal','700215'),(1,28,6,'Covenas','700220'),
(1,28,7,'Chalan','700230'),(1,28,8,'El Roble','700233'),(1,28,9,'Galeras','700235'),
(1,28,10,'Guaranda','700265'),(1,28,11,'La Union','700400'),(1,28,12,'Los Palmitos','700418'),
(1,28,13,'Majagual','700429'),(1,28,14,'Morroa','700473'),(1,28,15,'Ovejas','700508'),
(1,28,16,'Palmito','700523'),(1,28,17,'Sampues','700670'),(1,28,18,'San Benito Abad','700678'),
(1,28,19,'San Juan de Betulia','700702'),(1,28,20,'San Marcos','700708'),(1,28,21,'San Onofre','700713'),
(1,28,22,'San Pedro','700717'),(1,28,23,'Since','700742'),(1,28,24,'Sucre','700771'),
(1,28,25,'Santiago de Tolu','700820'),(1,28,26,'Tolu Viejo','700823');
-- TOLIMA - 47 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,29,1,'Ibague','730001'),(1,29,2,'Alpujarra','730030'),(1,29,3,'Alvarado','730043'),
(1,29,4,'Ambalema','730047'),(1,29,5,'Anzoategui','730053'),(1,29,6,'Armero','730067'),
(1,29,7,'Ataco','730073'),(1,29,8,'Cajamarca','730120'),(1,29,9,'Carmen de Apicala','730148'),
(1,29,10,'Casabianca','730152'),(1,29,11,'Chaparral','730168'),(1,29,12,'Coello','730200'),
(1,29,13,'Coyaima','730217'),(1,29,14,'Cunday','730226'),(1,29,15,'Dolores','730236'),
(1,29,16,'Espinal','730240'),(1,29,17,'Falan','730261'),(1,29,18,'Flandes','730270'),
(1,29,19,'Fresno','730275'),(1,29,20,'Guamo','730307'),(1,29,21,'Herveo','730339'),
(1,29,22,'Honda','730347'),(1,29,23,'Icononzo','730349'),(1,29,24,'Lerida','730411'),
(1,29,25,'Libano','730418'),(1,29,26,'Mariquita','730433'),(1,29,27,'Melgar','730443'),
(1,29,28,'Murillo','730461'),(1,29,29,'Natagaima','730483'),(1,29,30,'Ortega','730506'),
(1,29,31,'Palocabildo','730520'),(1,29,32,'Piedras','730547'),(1,29,33,'Planadas','730555'),
(1,29,34,'Prado','730563'),(1,29,35,'Purificacion','730580'),(1,29,36,'Rioblanco','730616'),
(1,29,37,'Roncesvalles','730622'),(1,29,38,'Rovira','730624'),(1,29,39,'Saldana','730670'),
(1,29,40,'San Antonio','730678'),(1,29,41,'San Luis','730686'),(1,29,42,'Santa Isabel','730720'),
(1,29,43,'Suarez','730775'),(1,29,44,'Valle de San Juan','730853'),(1,29,45,'Venadillo','730861'),
(1,29,46,'Villahermosa','730870'),(1,29,47,'Villarrica','730874');

-- VALLE DEL CAUCA - 42 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,30,1,'Cali','760001'),(1,30,2,'Alcala','760020'),(1,30,3,'Andalucia','760036'),
(1,30,4,'Ansermanuevo','760041'),(1,30,5,'Argelia','760054'),(1,30,6,'Bolivar','760100'),
(1,30,7,'Buenaventura','760109'),(1,30,8,'Guadalajara de Buga','760113'),(1,30,9,'Bugalagrande','760122'),
(1,30,10,'Caicedonia','760126'),(1,30,11,'Calima','760130'),(1,30,12,'Candelaria','760147'),
(1,30,13,'Cartago','760148'),(1,30,14,'Dagua','760233'),(1,30,15,'El Aguila','760243'),
(1,30,16,'El Cairo','760246'),(1,30,17,'El Cerrito','760248'),(1,30,18,'El Dovio','760250'),
(1,30,19,'Florida','760275'),(1,30,20,'Ginebra','760306'),(1,30,21,'Guacari','760315'),
(1,30,22,'Jamundi','760364'),(1,30,23,'La Cumbre','760377'),(1,30,24,'La Union','760400'),
(1,30,25,'La Victoria','760403'),(1,30,26,'Obando','760497'),(1,30,27,'Palmira','760520'),
(1,30,28,'Pradera','760563'),(1,30,29,'Restrepo','760606'),(1,30,30,'Riofrio','760616'),
(1,30,31,'Roldanillo','760622'),(1,30,32,'San Pedro','760670'),(1,30,33,'Sevilla','760736'),
(1,30,34,'Toro','760823'),(1,30,35,'Trujillo','760828'),(1,30,36,'Tulua','760834'),
(1,30,37,'Ulloa','760845'),(1,30,38,'Versalles','760863'),(1,30,39,'Vijes','760869'),
(1,30,40,'Yotoco','760890'),(1,30,41,'Yumbo','760892'),(1,30,42,'Zarzal','760895');

-- VAUPES - 3 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,31,1,'Mitu','970001'),(1,31,2,'Caruru','970015'),(1,31,3,'Pacoa','970020');

-- VICHADA - 4 municipios
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,32,1,'Puerto Carreno','990001'),(1,32,2,'La Primavera','990005'),
(1,32,3,'Santa Rosalia','990006'),(1,32,4,'Cumaribo','990007');

-- BOGOTA D.C. - 1 municipio
INSERT INTO tab_ciudades (id_pais, id_departamento, id_ciudad, nom_ciudad, zip_ciudad) VALUES
(1,33,1,'Bogota D.C.','110111');

