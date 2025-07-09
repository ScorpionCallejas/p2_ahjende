CREATE DATABASE IF NOT EXISTS database_usuarios;
USE database_usuarios;

CREATE TABLE ejecutivo (
    id_eje INT AUTO_INCREMENT PRIMARY KEY,
    nom_eje VARCHAR(100) NOT NULL,
    tel_eje VARCHAR(20) NOT NULL
);

CREATE TABLE cita (
    id_cit INT AUTO_INCREMENT PRIMARY KEY,
    hor_cit TIME DEFAULT CURRENT_TIME,
    cit_cit DATE DEFAULT CURRENT_DATE;
    nom_cit VARCHAR(100) NOT NULL,
    tel_cit VARCHAR(20),
    id_eje2 INT,
    FOREIGN KEY (id_eje2) REFERENCES ejecutivo(id_eje)
);
