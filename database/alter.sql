ALTER TABLE ordine_ricorrente 
ADD COLUMN     Frequenza ENUM('Giornaliera', 'Settimanale', 'Mensile') NOT NULL
 
ALTER TABLE ordine_ricorrente 
ADD COLUMN    GiorniSettimana VARCHAR(50)