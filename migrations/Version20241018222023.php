<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018222023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_order_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE delivery_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE delivery_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE geocoded_address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tour_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE unavailability_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vehicle_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE warehouse_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7440455979B1AD6 ON client (company_id)');
        $this->addSql('CREATE TABLE client_order (id INT NOT NULL, company_id INT NOT NULL, client_id INT NOT NULL, status_id INT DEFAULT NULL, order_number VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expected_delivery_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_56440F2F551F0F81 ON client_order (order_number)');
        $this->addSql('CREATE INDEX IDX_56440F2F979B1AD6 ON client_order (company_id)');
        $this->addSql('CREATE INDEX IDX_56440F2F19EB6921 ON client_order (client_id)');
        $this->addSql('CREATE INDEX IDX_56440F2F6BF700BD ON client_order (status_id)');
        $this->addSql('COMMENT ON COLUMN client_order.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN client_order.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN client_order.expected_delivery_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, name VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, contact_email VARCHAR(50) NOT NULL, contact_phone VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094FCAB86C7B ON company (contact_email)');
        $this->addSql('COMMENT ON COLUMN company.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN company.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE delivery (id INT NOT NULL, tour_id INT DEFAULT NULL, company_id INT NOT NULL, client_order_id INT NOT NULL, geocoded_address_id INT NOT NULL, status_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expected_delivery_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, actual_delivery_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3781EC10A3795DFD ON delivery (client_order_id)');
        $this->addSql('CREATE INDEX IDX_3781EC101B517A96 ON delivery (geocoded_address_id)');
        $this->addSql('CREATE INDEX idx_delivery_tour_id ON delivery (tour_id)');
        $this->addSql('CREATE INDEX idx_delivery_company_id ON delivery (company_id)');
        $this->addSql('CREATE INDEX idx_delivery_status ON delivery (status_id)');
        $this->addSql('CREATE INDEX idx_delivery_expected_delivery_date ON delivery (expected_delivery_date)');
        $this->addSql('CREATE INDEX idx_delivery_actual_delivery_date ON delivery (actual_delivery_date)');
        $this->addSql('COMMENT ON COLUMN delivery.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN delivery.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN delivery.expected_delivery_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN delivery.actual_delivery_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE delivery_product (id INT NOT NULL, product_id INT NOT NULL, delivery_id INT NOT NULL, quantity NUMERIC(14, 3) NOT NULL, temperature NUMERIC(5, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D954BB734584665A ON delivery_product (product_id)');
        $this->addSql('CREATE INDEX IDX_D954BB7312136921 ON delivery_product (delivery_id)');
        $this->addSql('CREATE TABLE driver (id INT NOT NULL, company_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, license_number VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_driver_company ON driver (company_id)');
        $this->addSql('CREATE INDEX idx_driver_license_number ON driver (license_number)');
        $this->addSql('CREATE INDEX idx_driver_first_name ON driver (first_name)');
        $this->addSql('CREATE INDEX idx_driver_last_name ON driver (last_name)');
        $this->addSql('COMMENT ON COLUMN driver.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN driver.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE geocoded_address (id INT NOT NULL, company_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, street_name VARCHAR(255) NOT NULL, full_address TEXT DEFAULT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(20) NOT NULL, department VARCHAR(10) DEFAULT NULL, country VARCHAR(100) NOT NULL, latitude NUMERIC(10, 8) DEFAULT NULL, longitude NUMERIC(11, 8) DEFAULT NULL, street_number VARCHAR(10) NOT NULL, is_verified BOOLEAN NOT NULL, source VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A064104A979B1AD6 ON geocoded_address (company_id)');
        $this->addSql('COMMENT ON COLUMN geocoded_address.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN geocoded_address.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, weight_kg NUMERIC(10, 3) DEFAULT NULL, is_hazardous BOOLEAN NOT NULL, hazard_class VARCHAR(50) DEFAULT NULL, adr_compliant BOOLEAN DEFAULT false NOT NULL, type VARCHAR(255) NOT NULL, density_kg_per_liter NUMERIC(10, 3) DEFAULT NULL, is_temperature_sensitive BOOLEAN DEFAULT NULL, thermal_expansion_coefficient_per_degree_celsius NUMERIC(10, 6) DEFAULT NULL, length_cm NUMERIC(10, 2) DEFAULT NULL, width_cm NUMERIC(10, 2) DEFAULT NULL, height_cm NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD979B1AD6 ON product (company_id)');
        $this->addSql('CREATE TABLE product_category (id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CDFC7356979B1AD6 ON product_category (company_id)');
        $this->addSql('CREATE TABLE reason (id INT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BB8880C5E237E06 ON reason (name)');
        $this->addSql('CREATE INDEX idx_reason_type ON reason (type)');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE status (id INT NOT NULL, name VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX status_unique ON status (name, type)');
        $this->addSql('CREATE TABLE tour (id INT NOT NULL, driver_id INT DEFAULT NULL, company_id INT NOT NULL, vehicle_id INT DEFAULT NULL, loading_id INT DEFAULT NULL, status_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, tour_number VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AD1F969A31EF8BE ON tour (tour_number)');
        $this->addSql('CREATE INDEX IDX_6AD1F969FE3B4E08 ON tour (loading_id)');
        $this->addSql('CREATE INDEX idx_tour_status ON tour (status_id)');
        $this->addSql('CREATE INDEX idx_tour_driver_id ON tour (driver_id)');
        $this->addSql('CREATE INDEX idx_tour_company_id ON tour (company_id)');
        $this->addSql('CREATE INDEX idx_tour_vehicle_id ON tour (vehicle_id)');
        $this->addSql('CREATE INDEX idx_tour_start_date ON tour (start_date)');
        $this->addSql('CREATE INDEX idx_tour_end_date ON tour (end_date)');
        $this->addSql('COMMENT ON COLUMN tour.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tour.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tour.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tour.end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE unavailability (id INT NOT NULL, driver_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, reason_id INT NOT NULL, company_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F0016D1979B1AD6 ON unavailability (company_id)');
        $this->addSql('CREATE INDEX idx_driver ON unavailability (driver_id)');
        $this->addSql('CREATE INDEX idx_vehicle ON unavailability (vehicle_id)');
        $this->addSql('CREATE INDEX idx_reason ON unavailability (reason_id)');
        $this->addSql('CREATE INDEX idx_start_date ON unavailability (start_date)');
        $this->addSql('CREATE INDEX idx_end_date ON unavailability (end_date)');
        $this->addSql('COMMENT ON COLUMN unavailability.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN unavailability.end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN unavailability.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN unavailability.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, company_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649979B1AD6 ON "user" (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE vehicle (id INT NOT NULL, company_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, license_plate VARCHAR(50) DEFAULT NULL, weight NUMERIC(10, 2) NOT NULL, volume NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_vehicle_company ON vehicle (company_id)');
        $this->addSql('CREATE INDEX idx_vehicle_license_plate ON vehicle (license_plate)');
        $this->addSql('COMMENT ON COLUMN vehicle.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vehicle.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE warehouse (id INT NOT NULL, address_id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ECB38BFCF5B7AF75 ON warehouse (address_id)');
        $this->addSql('CREATE INDEX IDX_ECB38BFC979B1AD6 ON warehouse (company_id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_order ADD CONSTRAINT FK_56440F2F979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_order ADD CONSTRAINT FK_56440F2F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_order ADD CONSTRAINT FK_56440F2F6BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC1015ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10A3795DFD FOREIGN KEY (client_order_id) REFERENCES client_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC101B517A96 FOREIGN KEY (geocoded_address_id) REFERENCES geocoded_address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC106BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery_product ADD CONSTRAINT FK_D954BB734584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery_product ADD CONSTRAINT FK_D954BB7312136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE geocoded_address ADD CONSTRAINT FK_A064104A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC7356979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969FE3B4E08 FOREIGN KEY (loading_id) REFERENCES warehouse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F9696BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D159BB1592 FOREIGN KEY (reason_id) REFERENCES reason (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE warehouse ADD CONSTRAINT FK_ECB38BFCF5B7AF75 FOREIGN KEY (address_id) REFERENCES geocoded_address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE warehouse ADD CONSTRAINT FK_ECB38BFC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE client_order_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE delivery_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE delivery_product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE geocoded_address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tour_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE unavailability_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE vehicle_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE warehouse_id_seq CASCADE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455979B1AD6');
        $this->addSql('ALTER TABLE client_order DROP CONSTRAINT FK_56440F2F979B1AD6');
        $this->addSql('ALTER TABLE client_order DROP CONSTRAINT FK_56440F2F19EB6921');
        $this->addSql('ALTER TABLE client_order DROP CONSTRAINT FK_56440F2F6BF700BD');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC1015ED8D43');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC10979B1AD6');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC10A3795DFD');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC101B517A96');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC106BF700BD');
        $this->addSql('ALTER TABLE delivery_product DROP CONSTRAINT FK_D954BB734584665A');
        $this->addSql('ALTER TABLE delivery_product DROP CONSTRAINT FK_D954BB7312136921');
        $this->addSql('ALTER TABLE driver DROP CONSTRAINT FK_11667CD9979B1AD6');
        $this->addSql('ALTER TABLE geocoded_address DROP CONSTRAINT FK_A064104A979B1AD6');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC7356979B1AD6');
        $this->addSql('ALTER TABLE tour DROP CONSTRAINT FK_6AD1F969C3423909');
        $this->addSql('ALTER TABLE tour DROP CONSTRAINT FK_6AD1F969979B1AD6');
        $this->addSql('ALTER TABLE tour DROP CONSTRAINT FK_6AD1F969545317D1');
        $this->addSql('ALTER TABLE tour DROP CONSTRAINT FK_6AD1F969FE3B4E08');
        $this->addSql('ALTER TABLE tour DROP CONSTRAINT FK_6AD1F9696BF700BD');
        $this->addSql('ALTER TABLE unavailability DROP CONSTRAINT FK_F0016D1C3423909');
        $this->addSql('ALTER TABLE unavailability DROP CONSTRAINT FK_F0016D1545317D1');
        $this->addSql('ALTER TABLE unavailability DROP CONSTRAINT FK_F0016D159BB1592');
        $this->addSql('ALTER TABLE unavailability DROP CONSTRAINT FK_F0016D1979B1AD6');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649979B1AD6');
        $this->addSql('ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486979B1AD6');
        $this->addSql('ALTER TABLE warehouse DROP CONSTRAINT FK_ECB38BFCF5B7AF75');
        $this->addSql('ALTER TABLE warehouse DROP CONSTRAINT FK_ECB38BFC979B1AD6');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_order');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE delivery_product');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE geocoded_address');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE reason');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE tour');
        $this->addSql('DROP TABLE unavailability');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP TABLE warehouse');
    }
}
