CREATE TABLE /*_*/user_key_value (
    ukv_user INTEGER UNSIGNED NOT NULL,
    ukv_key VARBINARY(255) NOT NULL,
    ukv_value BLOB,
    PRIMARY KEY(ukv_user,ukv_key)
) /*$wgDBTableOptions*/;
