BEGIN
    IF :matiere = 'VERRE' THEN
        insert into T_COLLECTE
        select to_DATE (a.C_DATE || ' ' ||a.C_HEURE||':00', 'DD/MM/YYYY HH24:MI:SS'),
               a.C_PATE,
               a.C_NOMGROUPEMENT,
               b.ID_COMMUNE,
               a.C_LOCALITE,
               b.ID_SITE,
               a.C_MATIERE,
               a.C_VOLUME,
               to_number (a.C_COLLECTE)
          from T_DATA_COLLECTE a, T_PRESTATAIRE b, T_SITE c
         where a.C_PATE = b.NO_CONTENEUR
            and b.ID_SITE = c.ID_SITE
            and c.USED_SITE = 1;
    ELSE
        insert into T_COLLECTE
        select to_DATE (a.C_DATE || ' ' ||a.C_HEURE||':00', 'DD/MM/YYYY HH24:MI:SS'),
               a.C_PATE,
               a.C_NOMGROUPEMENT,
               c.ID_COMMUNE,
               a.C_LOCALITE,
               c.ID_SITE,
               a.C_MATIERE,
               a.C_VOLUME,
               to_number (a.C_COLLECTE)
          from T_DATA_COLLECTE a, T_SITE c
         where a.ID_SITE = c.ID_SITE
            and c.USED_SITE = 1;
    ENDIF;
END