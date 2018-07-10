<?php
namespace Otdr\MageApiSubiektGt\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('otdr_mageapisubiektgt')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('otdr_mageapisubiektgt')
			)
				->addColumn(
					'id_gt_order',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'autincrement' => true,
						'primary'  => true,
						'unsigned' => true,
					],
					'Id order to subiekt'
				)
				->addColumn(
					'id_order',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['nullable => false'],
					'Magento order ID'
				)
				->addColumn(
					'gt_order_sent',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					['default'=>0],
					'Send order to magento ?'
				)
				->addColumn(
					'gt_sell_doc_request',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					['default'=>0],
					'Send request to create invoice or bill'
				)
				->addColumn(
					'gt_sell_doc_pdf_request',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					['default'=>0],
					'Send request to create pdf from invoice or bill'
				)
				->addColumn(
					'email_sell_doc_pdf_sent',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					['default'=>0],
					'Send to client selling document in pdf'
				)
				->addColumn(
					'gt_order_ref',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					20,
					[],
					'Subiekt order reference'
				)
				->addColumn(
					'gt_sell_doc_ref',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					20,
					[],
					'Subiekt invoice bill reference'
				)
				->addColumn(
					'doc_file_pdf_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					50,
					[],
					'Invoice, bill pdf version file name'
				)
				->addColumn(
					'add_date',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)
				->addColumn(
					'upd_date',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => true],
					'Updated At')
				->addColumn(
					'is_locked',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					1,
					['nullable' => false, 'default' => 0],
					'Updated At')
				->setComment('Post Table');
			$installer->getConnection()->createTable($table);
			$installer->getConnection()->addIndex(
				$installer->getTable('otdr_mageapisubiektgt'),
				$setup->getIdxName(
					$installer->getTable('otdr_mageapisubiektgt'),
					['id_order', 'gt_order_ref', 'is_locked', 'gt_order_sent', 'gt_sell_doc_sent','gt_sell_pdf_request','email_sell_pdf_sent'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
				),
				['id_order', 'gt_order_ref', 'is_locked', 'gt_order_sent', 'gt_sell_doc_sent','gt_sell_pdf_request','email_sell_pdf_sent'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
			);
		}
		$installer->endSetup();
	}
}
?>