import { Button, Menu, PageHeader, Space, Dropdown, Modal, Tag, Typography } from 'antd';
import React, { useRef, useState } from 'react';
import PageWrapper from '../layout/PageWrapper';
import ProTable from '@ant-design/pro-table';
import { DownOutlined, ExclamationCircleOutlined, CheckOutlined, CloseOutlined, DeleteOutlined } from '@ant-design/icons';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';
import moment from 'moment';

const { confirm } = Modal;

const Comments = () => {
    const [loading, setLoading] = useState(false);
    const actionRef = useRef();

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
                HTTP.delete(Routes.api.admin.blogComments, {
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

    const updateApproval = (row, isApproved) => {
        setLoading(true);
        HTTP.put(Routes.api.admin.blogComments + `/${row.id}`, {
            id: row.id,
            is_approved: isApproved ? 1 : 0
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
    };

    const menu = (row) => (
        <Menu>
            {
                row.is_approved ? (
                    <Menu.Item
                        key="0"
                        onClick={() => updateApproval(row, false)}
                        icon={<CloseOutlined />}
                    >
                        Unapprove
                    </Menu.Item>
                ) : (
                    <Menu.Item
                        key="0"
                        onClick={() => updateApproval(row, true)}
                        icon={<CheckOutlined />}
                    >
                        Approve
                    </Menu.Item>
                )
            }
            <Menu.Item 
                key="1"
                onClick={() => showConfirm([row])}
                icon={<DeleteOutlined />}
            >
                Delete
            </Menu.Item>
        </Menu>
    );

    const columns = [
        {
            title: 'Name',
            dataIndex: 'name',
            search: true,
            sorter: true,
            width: 160,
            ellipsis: true
        },
        {
            title: 'Email',
            dataIndex: 'email',
            search: true,
            sorter: false,
            width: 180,
            ellipsis: true
        },
        {
            title: 'Comment',
            dataIndex: 'body',
            search: true,
            sorter: false,
            ellipsis: true
        },
        {
            title: 'Post',
            dataIndex: ['post', 'title'],
            search: false,
            sorter: false,
            width: 200,
            ellipsis: true,
            render: (_, row) => row.post ? row.post.title : '-',
        },
        {
            title: 'Status',
            dataIndex: 'is_approved',
            sorter: true,
            width: 120,
            search: false,
            render: (value) => value ? <Tag color="green">Approved</Tag> : <Tag color="orange">Pending</Tag>,
        },
        {
            title: 'Created',
            dataIndex: 'created_at',
            sorter: true,
            width: 160,
            search: false,
            render: (value) => value ? moment(value).format('YYYY-MM-DD') : '-',
        },
        {
            title: 'Option',
            valueType: 'option',
            align: 'center',
            width: 160,
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

    return (
        <React.Fragment>
            <PageWrapper>
                <PageHeader
                    style={{padding: 0}}
                    title="Blog Comments"
                    subTitle={
                        <Typography.Text
                            style={{ width: '100%', color: 'grey' }}
                            ellipsis={{ tooltip: 'Moderate comments before publishing' }}
                        >
                            Moderate comments before publishing
                        </Typography.Text>
                    }
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
                            return HTTP.get(Routes.api.admin.blogComments+'?page='+params.current, {
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
        </React.Fragment>
    );
};

export default Comments;
