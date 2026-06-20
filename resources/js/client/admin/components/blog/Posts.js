import { Button, Menu, PageHeader, Space, Dropdown, Modal, Tag, Typography, Avatar } from 'antd';
import React, { useRef, useState } from 'react';
import PageWrapper from '../layout/PageWrapper';
import ProTable from '@ant-design/pro-table';
import { DownOutlined, ExclamationCircleOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';
import Post from './Post';
import moment from 'moment';

const { confirm } = Modal;

const Posts = () => {
    const [loading, setLoading] = useState(false);
    const actionRef = useRef();
    const [modalVisible, setModalVisible] = useState(false);
    const [itemToEdit, setItemToEdit] = useState(null);

    const statusTag = (status) => {
        if (status === 'published') {
            return <Tag color="green">Published</Tag>;
        }
        if (status === 'disabled') {
            return <Tag color="red">Disabled</Tag>;
        }
        return <Tag color="orange">Draft</Tag>;
    };

    const columns = [
        {
            title: 'Title',
            dataIndex: 'title',
            search: true,
            sorter: true,
            width: 220,
            ellipsis: true
        },
        {
            title: 'Cover',
            dataIndex: 'cover_image',
            sorter: false,
            align: 'center',
            width: 90,
            search: false,
            render: (_, row) => (
                row.cover_image_url ? (
                    <Avatar
                        shape="square"
                        size={48}
                        src={row.cover_image_url}
                    />
                ) : '-'
            ),
        },
        {
            title: 'Category',
            dataIndex: ['category', 'name'],
            sorter: false,
            width: 160,
            search: false,
            render: (_, row) => row.category ? row.category.name : '-',
        },
        {
            title: 'Tags',
            dataIndex: 'tags',
            sorter: false,
            width: 200,
            search: false,
            render: (_, row) => (
                row.tags && row.tags.length ? row.tags.map(tag => (
                    <Tag key={tag.id}>{tag.name}</Tag>
                )) : '-'
            ),
        },
        {
            title: 'Status',
            dataIndex: 'status',
            sorter: true,
            width: 120,
            search: false,
            render: (value) => statusTag(value),
        },
        {
            title: 'Published',
            dataIndex: 'published_at',
            sorter: true,
            width: 160,
            search: false,
            render: (value) => value ? moment(value).format('YYYY-MM-DD') : '-',
        },
        {
            title: 'Views',
            dataIndex: 'views_count',
            sorter: true,
            align: 'right',
            width: 100,
            search: false,
            render: (value) => Number(value || 0).toLocaleString(),
        },
        {
            title: 'Option',
            valueType: 'option',
            align: 'center',
            width: 170,
            fixed: 'right',
            render: (text, row) => [
                <Dropdown key="0" overlay={menu(row)} trigger={['click']}>
                    <a className="ant-dropdown-link" onClick={e => e.preventDefault()}>
                        Option <DownOutlined />
                    </a>
                </Dropdown>,
            ],
        }
    ];

    const showConfirm = (rows) => {
        let ids = [];
        rows.forEach(row => {
            ids.push(row.id);
        });
        confirm({
            confirmLoading: loading,
            title: `Do you want to delete ${ids.length == 1 ? 'this' : 'these'} ${ids.length == 1 ? 'item' : 'items'}?`,
            icon: <ExclamationCircleOutlined />,
            mask: true,
            onOk() {
                setLoading(true);
                HTTP.delete(Routes.api.admin.blogPosts, {
                    params: {
                        ids: ids
                    }
                })
                .then(response => {
                    Utils.handleSuccessResponse(response, () => {
                        Utils.showTinyNotification(response.data.message, 'success');
                        actionRef.current?.reloadAndRest();
                    });
                })
                .catch((error) => {
                    Utils.handleException(error);
                }).finally(() => {
                    setLoading(false);
                });
            },
        });
    };

    const menu = (row) => (
        <Menu>
            <Menu.Item 
                key="0" 
                onClick={() => {
                    setItemToEdit(row);
                    setModalVisible(true);
                }}
                icon={<EditOutlined />}
            >
                Edit
            </Menu.Item>
            <Menu.Item 
                key="1"
                onClick={() => showConfirm([row])}
                icon={<DeleteOutlined />}
            >
                Delete
            </Menu.Item>
        </Menu>
    );

    return (
        <React.Fragment>
            <PageWrapper>
                <PageHeader
                    style={{padding: 0}}
                    title="Blog Posts"
                    subTitle={
                        <Typography.Text
                            style={{ width: '100%', color: 'grey' }}
                            ellipsis={{ tooltip: 'Manage your blog posts' }}
                        >
                            Manage your blog posts
                        </Typography.Text>
                    }
                    extra={[
                        <Button key="add" type="primary" onClick={() => setModalVisible(true)}>
                            Add New
                        </Button>,
                    ]}
                >
                    <ProTable
                        columns={columns}
                        cardBordered={true}
                        showSorterTooltip={false}
                        scroll={{x: true}}
                        tableLayout={'fixed'}
                        pagination={{
                            showQuickJumper: true,
                            pageSize: 10
                        }}
                        rowSelection={{
                            // onChange: (_, selectedRows) => setSelectedRows(selectedRows),
                        }}
                        tableAlertRender={({ selectedRowKeys, onCleanSelected }) => (
                            <Space>
                                <span>
                                    Selected {selectedRowKeys.length} items
                                    <a
                                        style={{
                                            marginLeft: 8,
                                        }}
                                        onClick={onCleanSelected}
                                    >
                                        <strong>Cancel Selection</strong>
                                    </a>
                                </span>
                            </Space>
                        )}
                        tableAlertOptionRender={({ selectedRows }) => (
                            <Space>
                                <Button type="primary" onClick={() => showConfirm(selectedRows)}>Batch Deletion</Button>
                            </Space>
                        )}
                        actionRef={actionRef}
                        request={async (params, sorter) => {
                            return HTTP.get(Routes.api.admin.blogPosts+'?page='+params.current, {
                                params: {
                                    params,
                                    sorter,
                                    columns
                                }
                            }).then(response => {
                                return Utils.handleSuccessResponse(response, () => {
                                    return response.data.payload;
                                });
                            })
                            .catch(error => {
                                Utils.handleException(error);
                            });
                        }}
                        dateFormatter="string"
                        search={false}
                        rowKey="id"
                        options={{
                            search: true,
                        }}
                    />
                </PageHeader>
            </PageWrapper>
            {
                modalVisible && (
                    <Post
                        title={itemToEdit ? 'Edit Post' : 'Add Post'}
                        itemToEdit={itemToEdit}
                        visible={modalVisible}
                        handleCancel={
                            () => {
                                setItemToEdit(null);
                                setModalVisible(false);
                            }
                        }
                        submitCallback={
                            () => {
                                setItemToEdit(null);
                                actionRef.current?.reloadAndRest();
                                setModalVisible(false);
                            }
                        }
                    />
                )
            }
        </React.Fragment>
    );
};

export default Posts;
